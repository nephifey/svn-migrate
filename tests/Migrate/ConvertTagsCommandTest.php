<?php

namespace Tests\SvnMigrate\Migrate;

use Exception;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use SvnMigrate\Migrate\CloneCommand;
use SvnMigrate\Migrate\ConvertTagsCommand;
use SvnMigrate\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ConvertTagsCommandTest extends TestCase {

    private static string $cwd = "svnmigrate-test";

    private static string $prefix = "";

    /**
     * The ConvertTagsCommand relies on a cloned repository in order to test properly,
     * which is why the setup is executing the CloneCommand.
     * @throws Exception
     */
    public static function setUpBeforeClass(): void {
        $commandTester = new CommandTester(new CloneCommand());
        $exitCode = $commandTester->execute([
            "svn-repo-url"    => "https://svn.riouxsvn.com/svnmigrate-test",
            "--prefix"        => self::$prefix,
        ]);

        if (Command::SUCCESS !== $exitCode)
            throw new Exception(sprintf(
                "Failed to setup %s, could not clone the repository in %s.",
                self::class, __METHOD__,
            ));
    }

    public static function tearDownAfterClass(): void {
        Util::rmDirRecursive(self::$cwd);
    }

    public function testConvertTagsCommand() {
        $commandTester = new CommandTester(new ConvertTagsCommand());
        $exitCode = $commandTester->execute([
            "cwd"    => self::$cwd,
            "--prefix" => self::$prefix,
        ]);
        $output = $commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertFalse(file_exists(self::$cwd . "/.git/refs/remotes/" . self::$prefix . "tags"));
        $this->assertTrue(file_exists(($localTagsPath = self::$cwd . "/.git/refs/tags")));

        $callback = function (SplFileInfo $splFileInfo) use ($output) {
            $this->assertStringContainsString($splFileInfo->getFilename(), $output);
        };

        Util::recurseDir($localTagsPath, $callback, FilesystemIterator::SKIP_DOTS);
    }
}

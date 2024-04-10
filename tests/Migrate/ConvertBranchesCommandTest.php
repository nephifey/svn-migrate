<?php

namespace Tests\SvnMigrate\Migrate;

use Exception;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use SvnMigrate\Migrate\CloneCommand;
use SvnMigrate\Migrate\ConvertBranchesCommand;
use SvnMigrate\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ConvertBranchesCommandTest extends TestCase {

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

    public function testConvertBranchesCommand() {
        $commandTester = new CommandTester(new ConvertBranchesCommand());
        $exitCode = $commandTester->execute([
            "cwd"    => self::$cwd,
            "--prefix" => self::$prefix,
        ]);
        $output = $commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertTrue(Util::isDirEmpty(self::$cwd . "/.git/refs/remotes/" . self::$prefix));
        $this->assertTrue(file_exists(($localBranchesPath = self::$cwd . "/.git/refs/heads")));

        $callback = function (SplFileInfo $splFileInfo) use ($output) {
            // We skip this because 'master' is created during the CloneCommand.
            // It is likely to not exist in the original remote branch deletions ($output).
            if ("master" === $splFileInfo->getFilename())
                return;

            $this->assertStringContainsString($splFileInfo->getFilename(), $output);
        };

        Util::recurseDir($localBranchesPath, $callback, FilesystemIterator::SKIP_DOTS);
    }
}

<?php

namespace Tests\SvnMigrate\Migrate;

use Exception;
use PHPUnit\Framework\TestCase;
use SvnMigrate\Migrate\AuthorCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class AuthorCommandTest extends TestCase {

    public function testFileValidity() {
        $commandTester = new CommandTester(new AuthorCommand());
        $exitCode = $commandTester->execute([
            "svn-repo-url"    => "https://svn.riouxsvn.com/svnmigrate-test",
            "--override-file" => true,
        ]);
        $authors = file("authors-file.txt");

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertIsArray($authors);
        $this->assertNotEmpty($authors);

        foreach ($authors as $author) {
            $authorParts = explode("=>", trim($author));
            $this->assertCount(2, $authorParts);
        }
    }

    /**
     * @dataProvider argValidityProvider
     */
    public function testArgValidity(array $args, bool $expectException, bool $isValid) {
        if ($expectException)
            $this->expectException(Exception::class);

        $commandTester = new CommandTester(new AuthorCommand());
        $exitCode = $commandTester->execute($args);

        if ($isValid) {
            $this->assertEquals(Command::SUCCESS, $exitCode);
            $this->assertTrue(file_exists($args["output-file"] ?? "authors-file.txt"));
        } else {
            $this->assertNotEquals(Command::SUCCESS, $exitCode);
        }
    }

    public static function argValidityProvider(): array {
        return [
            [[], true, false],
            [[
                "svn-repo-url" => "test",
            ], true, false],
            [[
                "svn-repo-url"    => "https://validurl.com",
                "--override-file" => true,
            ], false, false],
            [[
                "svn-repo-url"    => "https://svn.riouxsvn.com/svnmigrate-test",
                "--override-file" => true,
            ], false, true],
            [[
                "svn-repo-url"    => "https://svn.riouxsvn.com/svnmigrate-test",
                "--output-file"   => "authors-v1.txt",
                "--override-file" => true,
            ], false, true],
            [[
                "svn-repo-url" => "https://svn.riouxsvn.com/svnmigrate-test",
                "--username"   => null,
            ], true, false],
            [[
                "svn-repo-url" => "https://svn.riouxsvn.com/svnmigrate-test",
                "--email"      => null,
            ], true, false],
            [[
                "svn-repo-url"  => "https://svn.riouxsvn.com/svnmigrate-test",
                "--output-file" => null,
            ], true, false],
            [[
                "svn-repo-url"    => "https://svn.riouxsvn.com/svnmigrate-test",
            ], true, false],
        ];
    }
}

<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate\Setup;

use Nephifey\SvnMigrate\CommandStyle;
use Nephifey\SvnMigrate\Exception\SystemRequirementsException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SystemRequirements {

    private const COMMAND_CHECKS = [
        "git"     => ["git", "--version"],
        "svn"     => ["svn", "--version", "--quiet"],
        "git svn" => ["git", "svn", "--version"],
    ];

    /**
     * @param CommandStyle $cli Styled IO object.
     * @return void
     * @throws ProcessFailedException
     */
    static public function check(CommandStyle $cli): void {
        $cli->section("Checking system requirements.");

        foreach (self::COMMAND_CHECKS as $commandName => $command) {
            try {
                $process = new Process($command);
                $process->mustRun();

                $cli->success("{$commandName} version '" . trim($process->getOutput()) . "' is installed.");
            } catch (ProcessFailedException $exception) {
                throw new SystemRequirementsException($exception->getProcess());
            }
        }
    }
}

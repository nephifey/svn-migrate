<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate\Migrate\Process;

use Nephifey\SvnMigrate\Exception\MigrateException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class GitRemoveTrunkProcess extends AbstractProcess {

    public function runProcess(): void {
        $this->migrate->getCli()->section("Removing the duplicate local trunk.");

        if (!is_dir((string) $this->migrate->getAnswers()->getOutputDestination())) {
            throw new MigrateException("The output destination is not a directory, cannot remove duplicate local trunk.");
        }

        try {
            $args = [
                "PREFIX" => $this->migrate->getAnswers()->getGitPrefix(),
                "TRUNK"  => trim($this->migrate->getAnswers()->getSvnTrunk(), "/"),
            ];

            $process = Process::fromShellCommandline(
                'git branch -d "${:PREFIX}""${:TRUNK}"',
                $this->migrate->getAnswers()->getOutputDestination(),
                $args,
                null,
                null,
            );
            $process->mustRun([$this->migrate, "writeCommandOutputToCli"]);

            $this->migrate->getCli()->info("The duplicate trunk has been removed.");
        } catch (ProcessFailedException $exception) {
            throw new MigrateException("The '{$exception->getProcess()->getCommandLine()}' command failed.", $exception->getCode(), $exception);
        }
    }
}

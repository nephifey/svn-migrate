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

use Symfony\Component\Process\Process;

final class GitConvertBranchesProcess extends AbstractGitConvertProcess {

    protected static string $sectionName = "Converting remote branches into local branches.";

    protected function getRemotes(): array {
        $args = [
            "FORMAT" => "%(refname:short)",
            "PREFIX" => $this->migrate->getAnswers()->getGitPrefix(),
        ];

        $process = Process::fromShellCommandline(
            'git for-each-ref --format="${:FORMAT}" refs/remotes/"${:PREFIX}"',
            $this->migrate->getAnswers()->getOutputDestination(),
            $args,
            null,
            null,
        );
        $process->mustRun();

        return $this->normalizeRemotes($process->getOutput());
    }

    protected function createLocal(string $remote, ?callable $callback = null): void {
        $process = Process::fromShellCommandline(
            'git branch "${:REMOTE}" refs/remotes/"${:REMOTE}"',
            $this->migrate->getAnswers()->getOutputDestination(),
            ["REMOTE" => $remote],
            null,
            null,
        );
        $process->run($callback);
    }
}

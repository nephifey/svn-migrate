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

final class GitConvertTagsProcess extends AbstractGitConvertProcess {

    protected static string $sectionName = "Converting remote tags into local tags.";

    protected function getRemotes(): array {
        $args = [
            "FORMAT" => "%(refname:short)",
            "PREFIX" => $this->migrate->getAnswers()->getGitPrefix(),
            "TAGS"   => trim($this->migrate->getAnswers()->getSvnTags(), "/"),
        ];

        $process = Process::fromShellCommandline(
            'git for-each-ref --format="${:FORMAT}" refs/remotes/"${:PREFIX}""${:TAGS}"',
            $this->migrate->getAnswers()->getOutputDestination(),
            $args,
            null,
            null,
        );
        $process->mustRun();

        return $this->normalizeRemotes($process->getOutput());
    }

    protected function createLocal(string $remote, ?callable $callback = null): void {
        $tag = str_replace($this->migrate->getAnswers()->getGitPrefix(), "", $remote);
        $tag = str_replace(trim($this->migrate->getAnswers()->getSvnTags(), "/"), "", $tag);

        $args = [
            "REMOTE" => $remote,
            "TAG"    => trim($tag, "/"),
        ];

        $process = Process::fromShellCommandline(
            'git tag "${:TAG}" "${:REMOTE}"',
            $this->migrate->getAnswers()->getOutputDestination(),
            $args,
            null,
            null,
        );
        $process->mustRun($callback);
    }
}

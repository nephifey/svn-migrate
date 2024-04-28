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

final class GitSvnCloneProcess extends AbstractSvnProcess {

    protected static string $sectionName = "Migrating the repository.";

    protected function getRunCallback(): ?callable {
        return [$this->migrate, "writeCommandOutputToCli"];
    }

    protected function successCallback(Process $process): void {
        $this->migrate->getCli()->info("The migration has been completed.");
    }

    protected function buildSvnProcess(): Process {
        $command = 'git svn clone "${:SVN_REPO_URL}" --trunk="${:TRUNK}" --tags="${:TAGS}" --branches="${:BRANCHES}" --prefix="${:PREFIX}"';
        $args = [
            "SVN_REPO_URL" => $this->migrate->getAnswers()->getSvnRepositoryUrl(),
            "TRUNK"        => $this->migrate->getAnswers()->getSvnTrunk(),
            "TAGS"         => $this->migrate->getAnswers()->getSvnTags(),
            "BRANCHES"     => $this->migrate->getAnswers()->getSvnBranches(),
            "PREFIX"       => $this->migrate->getAnswers()->getGitPrefix(),
        ];

        if (!empty($this->migrate->getAnswers()->getSvnUsername())) {
            $args["USERNAME"] = $this->migrate->getAnswers()->getSvnUsername();
            $command .= ' --username="${:USERNAME}"';
        }

        if (file_exists((string) $this->migrate->getAuthorFilename())) {
            $args["AUTHOR_FILE"] = $this->migrate->getAuthorFilename();
            $command .= ' --authors-file="${:AUTHOR_FILE}"';
        }

        if (!$this->migrate->getAnswers()->hasMetadata()) {
            $command .= " --no-metadata";
        }

        if (!empty($this->migrate->getAnswers()->getOutputDestination())) {
            $args["OUTPUT_DEST"] = $this->migrate->getAnswers()->getOutputDestination();
            $command .= ' "${:OUTPUT_DEST}"';
        }

        return Process::fromShellCommandline(
            $command,
            null,
            $args,
            $this->migrate->getAnswers()->getSvnPassword(),
            null,
        );
    }
}

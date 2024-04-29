<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate\Migrate;

use Nephifey\SvnMigrate\CommandStyle;
use Nephifey\SvnMigrate\Exception\MigrateException;
use Nephifey\SvnMigrate\Migrate\Process\BuildAuthorsFileProcess;
use Nephifey\SvnMigrate\Migrate\Process\GitConvertBranchesProcess;
use Nephifey\SvnMigrate\Migrate\Process\GitConvertTagsProcess;
use Nephifey\SvnMigrate\Migrate\Process\GitRemoveTrunkProcess;
use Nephifey\SvnMigrate\Migrate\Process\GitSvnCloneProcess;
use Nephifey\SvnMigrate\Migrate\Process\MigrateProcessInterface;
use Nephifey\SvnMigrate\Setup\Answers;
use Symfony\Component\Filesystem\Exception\IOException;

final class Migrate {

    private Answers $answers;

    private CommandStyle $cli;

    private AuthorFile $authorFile;

    private bool $isWindows;

    /**
     * @throws MigrateException
     */
    public function __construct(Answers $answers, CommandStyle $cli) {
        $this->answers = $answers;
        $this->cli = $cli;
        $this->isWindows = ("\\" === DIRECTORY_SEPARATOR);

        try {
            $this->authorFile = new AuthorFile();
        } catch (IOException $exception) {
            throw new MigrateException("Could not create author file.", $exception->getCode(), $exception);
        }
    }

    public function getAnswers(): Answers {
        return $this->answers;
    }

    public function getCli(): CommandStyle {
        return $this->cli;
    }

    public function getAuthorFile(): AuthorFile {
        return $this->authorFile;
    }

    public function isWindows(): bool {
        return $this->isWindows;
    }

    public function writeCommandOutputToCli(string $type, string $data): void {
        $this->cli->write($data);
    }

    /**
     * @throws MigrateException
     */
    public function runProcesses(): void {
        foreach ($this->getProcesses() as $process) {
            $process->runProcess();
        }
    }

    /**
     * @return array<MigrateProcessInterface>
     */
    private function getProcesses(): array {
        return [
            new BuildAuthorsFileProcess($this),
            new GitSvnCloneProcess($this),
            new GitConvertTagsProcess($this),
            new GitConvertBranchesProcess($this),
            new GitRemoveTrunkProcess($this),
        ];
    }
}

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

final class Migrate {

    private Answers $answers;

    private CommandStyle $cli;

    /**
     * @var resource|false|null
     */
    private $authorFile;

    private ?string $authorFilename = null;

    /**
     * @throws MigrateException
     */
    public function __construct(Answers $answers, CommandStyle $cli) {
        $this->answers = $answers;
        $this->cli = $cli;

        $authorFile = tmpfile();
        if (false === $authorFile) {
            throw new MigrateException("Could not create author file.");
        }

        $this->authorFile = $authorFile;
        $this->authorFilename = stream_get_meta_data($this->authorFile)["uri"];
    }

    public function getAnswers(): Answers {
        return $this->answers;
    }

    public function getCli(): CommandStyle {
        return $this->cli;
    }

    /**
     * @return resource|false|null
     */
    public function getAuthorFile() {
        return $this->authorFile;
    }

    public function getAuthorFileName(): ?string {
        return $this->authorFilename;
    }

    public function resetAuthorFile(): void {
        $this->authorFile = null;
        $this->authorFilename = null;

        if (is_resource($this->authorFile)) {
            fclose($this->authorFile);
        }
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

        $this->resetAuthorFile();
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

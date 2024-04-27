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

abstract class AbstractGitConvertProcess extends AbstractProcess {

    static protected string $sectionName = "";

    static protected string $successMessage = "The remotes have been converted into locals.";

    /**
     * @return array<string>
     * @throws ProcessFailedException
     */
    abstract protected function getRemotes(): array;

    /**
     * @param string $remote
     * @param callable|null $callback
     * @return void
     * @throws ProcessFailedException
     */
    abstract protected function createLocal(string $remote, ?callable $callback = null): void;

    public function runProcess(): void {
        $this->migrate->getCli()->section(static::$sectionName);

        if (!is_dir((string) $this->migrate->getAnswers()->getOutputDestination())) {
            throw new MigrateException("The output destination is not a directory, cannot convert tags.");
        }

        try {
            foreach ($this->getRemotes() as $remote) {
                $this->createLocal($remote, [$this->migrate, "writeCommandOutputToCli"]);
                $this->deleteRemote($remote, [$this->migrate, "writeCommandOutputToCli"]);
            }

            $this->migrate->getCli()->info(static::$successMessage);
        } catch (ProcessFailedException $exception) {
            throw new MigrateException("The '{$exception->getProcess()->getCommandLine()}' command failed.", $exception->getCode(), $exception);
        }
    }

    /**
     * @param string $remote
     * @param callable|null $callback
     * @return void
     */
    protected function deleteRemote(string $remote, ?callable $callback = null): void {
        $process = Process::fromShellCommandline(
            'git branch -D -r "${:REMOTE}"',
            $this->migrate->getAnswers()->getOutputDestination(),
            ["REMOTE" => $remote],
            null,
            null,
        );
        $process->mustRun($callback);
    }

    /**
     * @param string $output
     * @return array<string>
     */
    protected function normalizeRemotes(string $output): array {
        return array_filter(
            explode(
                "\n",
                str_replace(["\r\n", "\n"], "\n", $output),
            )
        );
    }
}

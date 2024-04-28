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
use Nephifey\SvnMigrate\Setup\Question\SvnPasswordQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class AbstractSvnProcess extends AbstractProcess {

    static protected string $sectionName = "";

    abstract protected function buildSvnProcess(): Process;

    /**
     * @throws MigrateException
     */
    abstract protected function successCallback(Process $process): void;

    protected function getRunCallback(): ?callable {
        return null;
    }

    public function runProcess(): void {
        $this->migrate->getCli()->section(static::$sectionName);
        $this->runSvnProcess();
    }

    /**
     * @throws MigrateException
     */
    protected function runSvnProcess(int $attempt = 0, int $maxAttempts = 3): void {
        try {
            $process = $this->buildSvnProcess();
            $process->mustRun($this->getRunCallback());

            call_user_func([$this, "successCallback"], $process);
        } catch (ProcessFailedException $exception) {
            if (strpos($exception->getMessage(), "No more credentials or we tried too many times.") && $attempt < $maxAttempts) {
                $this->migrate->getAnswers()->setValue(
                    "svnPassword",
                    $this->migrate->getCli()->askQuestion(new SvnPasswordQuestion()),
                );

                $this->runSvnProcess(++$attempt, $maxAttempts);
                return;
            }

            throw new MigrateException("The '{$exception->getProcess()->getCommandLine()}' command failed.", $exception->getCode(), $exception);
        }
    }
}
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

use Exception;
use Nephifey\SvnMigrate\Exception\MigrateException;
use SimpleXMLElement;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class BuildAuthorsFileProcess extends AbstractSvnProcess {

    protected static string $sectionName = "Building authors file.";

    /**
     * @throws MigrateException
     */
    protected function successCallback(Process $process): void {
        $this->buildAuthorsFile($process->getOutput());

        $contents = $this->migrate->getAuthorFile()->getContents();
        if (false !== $contents) {
            $this->migrate->getCli()->write($contents);
        }

        $this->migrate->getCli()->info("The authors file has been built.");
    }

    protected function buildSvnProcess(): Process {
        $command = 'svn log "${:SVN_REPO_URL}" --xml --quiet';
        $args = ["SVN_REPO_URL" => $this->migrate->getAnswers()->getSvnRepositoryUrl()];

        if (!empty($this->migrate->getAnswers()->getSvnUsername())) {
            $args["USERNAME"] = $this->migrate->getAnswers()->getSvnUsername();
            $command .= ' --username="${:USERNAME}"';
        }

        if (!empty($this->migrate->getAnswers()->getSvnPassword())) {
            $args["PASSWORD"] = $this->migrate->getAnswers()->getSvnPassword();
            $command .= ' --password="${:PASSWORD}"';
        }

        return Process::fromShellCommandline($command, null, $args, $this->migrate->getAnswers()->getSvnPassword(), null);
    }

    /**
     * @throws MigrateException
     */
    private function buildAuthorsFile(string $authorsXml): void {
        if (empty($authorsXml)) {
            return;
        }

        try {
            $authors = $this->getAuthors($authorsXml);
            if (!empty($authors)) {
                $this->updateAuthorsFile($authors);
            }
        } catch (Exception $exception) {
            throw new MigrateException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param string $authorsXml
     * @return array<string>
     * @throws Exception
     */
    private function getAuthors(string $authorsXml): array {
        $authors = [];
        $simpleXml = new SimpleXMLElement($authorsXml);

        if (!isset($simpleXml->logentry)) {
            throw new Exception("No 'logentry' element found.");
        }

        foreach ($simpleXml->logentry as $logEntry) {
            if (!isset($logEntry->author)) {
                throw new Exception("No 'author' element found.");
            }

            if (!isset($authors[(string) $logEntry->author])) {
                $authors[(string) $logEntry->author] = (string) $logEntry->author;
            }
        }

        return $authors;
    }

    /**
     * @param array<string> $authors
     * @return void
     * @throws Exception
     */
    private function updateAuthorsFile(array $authors): void {
        if (!$this->migrate->getAuthorFile()->exists()) {
            return;
        }

        foreach ($authors as $author) {
            $this->migrate->getAuthorFile()
                ->write("{$author} => {$author} <{$author}@email.com>" . PHP_EOL);
        }

        $this->migrate->getAuthorFile()->closeStream();

        $command = (
            $this->migrate->isWindows()
            ? ["notepad", $this->migrate->getAuthorFile()->getName()]
            : ["vim", $this->migrate->getAuthorFile()->getName()]
        );

        try {
            $process = new Process($command, null, null, null, null);
            $process->setTty(Process::isTtySupported());
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new MigrateException("The '{$exception->getProcess()->getCommandLine()}' command failed.", $exception->getCode(), $exception);
        }
    }
}

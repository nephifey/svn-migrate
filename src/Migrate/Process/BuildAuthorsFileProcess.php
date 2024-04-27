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

final class BuildAuthorsFileProcess extends AbstractProcess {

    public function runProcess(): void {
        $this->migrate->getCli()->section("Building authors file.");

        $command = 'svn log "${:SVN_REPO_URL}" --xml --quiet';
        $args = ["SVN_REPO_URL" => $this->migrate->getAnswers()->getSvnRepositoryUrl()];

        if (!empty($this->migrate->getAnswers()->getSvnUsername())) {
            $args["USERNAME"] = $this->migrate->getAnswers()->getSvnUsername();
            $command .= ' --username="${:USERNAME}"';
        }

        try {
            $process = Process::fromShellCommandline($command, null, $args, null, null);
            $process->mustRun();
            $this->buildAuthorsFile($process->getOutput());

            $contents = file_get_contents((string) $this->migrate->getAuthorFileName());
            if (false !== $contents) {
                $this->migrate->getCli()->write($contents);
            }

            $this->migrate->getCli()->info("The authors file has been built.");
        } catch (ProcessFailedException $exception) {
            throw new MigrateException("The '{$exception->getProcess()->getCommandLine()}' command failed.", $exception->getCode(), $exception);
        }
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
        } catch (Exception $e) {
            throw new MigrateException($e->getMessage(), $e->getCode(), $e);
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
        if (!is_resource($this->migrate->getAuthorFile())) {
            return;
        }

        foreach ($authors as $author) {
            fwrite($this->migrate->getAuthorFile(), "{$author} => {$author} <{$author}@email.com>" . PHP_EOL);
        }

        $command = (
            "\\" === DIRECTORY_SEPARATOR
            ? ["notepad", $this->migrate->getAuthorFileName()]
            : ["vim", $this->migrate->getAuthorFileName()]
        );

        try {
            $process = new Process($command, null, null, null, null);
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new MigrateException("The '{$exception->getProcess()->getCommandLine()}' command failed.", $exception->getCode(), $exception);
        }
    }
}

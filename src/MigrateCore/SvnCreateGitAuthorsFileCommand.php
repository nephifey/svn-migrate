<?php

namespace SvnMigrate\MigrateCore;

use Exception;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SvnCreateGitAuthorsFileCommand extends Command {

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = "migrate:svn-create-git-authors-file";

    /**
     * {@inheritdoc}
     */
    protected static $defaultDescription = "Uses svn to create an authors file for git";

    /**
     * @var Process Svn log process.
     */
    private Process $process;

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->buildSvnLogCommand($input);
        $exitCode = $this->process->run();

        if (!$this->process->isSuccessful())
            throw new ProcessFailedException($this->process);

        $this->buildAuthorsFile($input);

        return 0 === $exitCode
            ? self::SUCCESS
            : self::FAILURE;
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        if (file_exists($input->getOption("output-file")) && !$input->getOption("override-file"))
            throw new Exception(sprintf(
                "The output file \"%s\" already exists. Please choose a different name or add --override-file.",
                $input->getOption("output-file"),
            ));
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->addArgument("svn-repo-url", InputArgument::OPTIONAL, "The svn repository url to clone");
        $this->addOption("username", "u", InputOption::VALUE_REQUIRED, "Username for the svn repository authentication");
        $this->addOption("email", "e", InputOption::VALUE_OPTIONAL, "Email address used for the map", "@company.com");
        $this->addOption("output-file", null, InputOption::VALUE_OPTIONAL, "The name of the output file", "authors-file.txt");
        $this->addOption("override-file", null, InputOption::VALUE_NEGATABLE, false);
        $this->setAliases(["migrate:author"]);
    }

    /**
     * @param InputInterface $input
     * @return void
     */
    protected function buildSvnLogCommand(InputInterface $input): void {
        $cmd = 'svn log --xml --quiet';
        $args = [];

        if (!empty($input->getOption("username"))) {
            $args["USERNAME"] = $input->getOption("username");
            $cmd .= ' --username="${:USERNAME}"';
        }

        if (!empty($input->getArgument("svn-repo-url"))) {
            $args["SVN_REPO_URL"] = $input->getArgument("svn-repo-url");
            $cmd .= ' "${:SVN_REPO_URL}"';
        }

        $this->process = Process::fromShellCommandline($cmd, null, $args, null, null);
    }

    /**
     * @param InputInterface $input
     * @return void
     * @throws Exception
     */
    protected function buildAuthorsFile(InputInterface $input): void {
        if (empty(($output = $this->process->getOutput())))
            return;

        try {
            $simpleXml = new SimpleXMLElement($output);

            if (!isset($simpleXml->logentry))
                throw new Exception("No 'logentry' element found.");

            foreach ($simpleXml->logentry as $logEntry) {
                if (!isset($logEntry->author))
                    throw new Exception("No 'author' element found.");

                if (!isset($authorsMap[(string) $logEntry->author]))
                    $authorsMap[(string) $logEntry->author] = (string) $logEntry->author;
            }

            if (!empty($authorsMap)) {
                if (file_exists($input->getOption("output-file")))
                    unlink($input->getOption("output-file"));

                $email = $input->getOption("email");
                $email = $email[0] !== "@" ? "@{$email}" : $email;
                $fp = fopen($input->getOption("output-file"), "w+");
                foreach ($authorsMap as $author) {
                    fwrite($fp, "{$author} => {$author} <{$author}{$email}>" . PHP_EOL);
                }
                fclose($fp);
            }
        } catch (Exception $e) {
            throw new Exception("Error while parsing the XML: {$e->getMessage()}", $e->getCode(), $e);
        }
    }
}

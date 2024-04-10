<?php

namespace SvnMigrate\Migrate;

use Exception;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

final class AuthorCommand extends Command {

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = "migrate:author";

    /**
     * {@inheritdoc}
     */
    protected static $defaultDescription = "Uses svn to create an authors file for git";

    /**
     * {@inheritdoc}
	 * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
		$process = $this->buildAuthorProcess($input);
		$exitCode = $process->run();

		if (self::SUCCESS === $exitCode && !$this->buildAuthorsFile($process, $input))
            $exitCode = self::FAILURE;

        return $exitCode;
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        $validator = Validation::createValidator();

        $violations = $validator->validate($input->getArgument("svn-repo-url"), [new Url()]);
        if (0 !== count($violations))
            throw new Exception($violations[0]);

        if (file_exists($input->getOption("output-file")) && !$input->getOption("override-file"))
			throw new Exception(sprintf(
				"The output file \"%s\" already exists. Use the [--|--author-]override-file option to ignore this error.",
				$input->getOption("output-file"),
			));
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->addArgument("svn-repo-url", InputArgument::REQUIRED, "The svn repository url to clone");
        $this->addOption("username", "u", InputOption::VALUE_REQUIRED, "Username for the svn repository authentication");
        $this->addOption("email", null, InputOption::VALUE_REQUIRED, "Email address used for the map", "@company.com");
        $this->addOption("output-file", null, InputOption::VALUE_REQUIRED, "The name of the output file", "authors-file.txt");
        $this->addOption("override-file", null, InputOption::VALUE_NEGATABLE, "If there is a file that exists override it instead of throwing an error", false);
    }

	/**
     * Builds the process for fetching authors.
	 * @param InputInterface $input
	 * @return Process
	 */
    protected function buildAuthorProcess(InputInterface $input): Process {
        $cmd = 'svn log "${:SVN_REPO_URL}" --xml --quiet';
        $args = ["SVN_REPO_URL" => $input->getArgument("svn-repo-url")];

        if (!empty($input->getOption("username"))) {
            $args["USERNAME"] = $input->getOption("username");
            $cmd .= ' --username="${:USERNAME}"';
        }

        return Process::fromShellCommandline($cmd, null, $args, null, null);
    }

	/**
     * Attempts to build an author file based on the author process output.
	 * @param Process $process
	 * @param InputInterface $input
	 * @return bool
	 * @throws Exception
	 */
    protected function buildAuthorsFile(Process $process, InputInterface $input): bool {
        if (empty(($output = $process->getOutput())))
            return false;

		try {
            $simpleXml = new SimpleXMLElement($output);

            if (!isset($simpleXml->logentry))
                throw new Exception("No \"logentry\" element found.");

            foreach ($simpleXml->logentry as $logEntry) {
                if (!isset($logEntry->author))
                    throw new Exception("No \"author\" element found.");

                if (!isset($authorsMap[(string) $logEntry->author]))
                    $authorsMap[(string) $logEntry->author] = (string) $logEntry->author;
            }

            if (!empty($authorsMap)) {
                if (file_exists($input->getOption("output-file")))
                    unlink($input->getOption("output-file"));

                $email = $input->getOption("email");
                $email = $email[0] !== "@" ? "@{$email}" : $email;
                $fp = fopen($input->getOption("output-file"), "w+");

				if (false === $fp)
					throw new Exception(sprintf(
						"Could not open \"%s\" for writing authors.",
						$input->getOption("output-file"),
					));

                foreach ($authorsMap as $author)
					fwrite($fp, "{$author} => {$author} <{$author}{$email}>" . PHP_EOL);

				if (false === fclose($fp))
					throw new Exception(sprintf(
						"Could not close \"%s\" properly.",
						$input->getOption("output-file")
					));

				return true;
            }
        } catch (Exception $e) {
            throw new Exception("Error while parsing the XML: {$e->getMessage()}", $e->getCode(), $e);
        }

		return false;
    }
}

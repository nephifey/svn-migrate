<?php

namespace SvnMigrate\MigrateCore;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class MigrateCoreCommand extends Command {

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = "migrate:core";

    /**
     * {@inheritdoc}
     */
    protected static $defaultDescription = "Executes all migration core commands";

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
		if (!$input->getOption("skip-author")) {
			$this->executeSvnCreateGitAuthorFileCommand($input, $output);
		} else {
			$output->writeln("--skip-author flag found, skipping [" . SvnCreateGitAuthorFileCommand::getDefaultName() . "] command.");
		}

		$this->executeGitSvnCloneCommand($input, $output);

        return self::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
		$this->addArgument("svn-repo-url", InputArgument::REQUIRED, "The svn repository url to clone");
		$this->addOption("username", "u", InputOption::VALUE_REQUIRED, "Username for the svn repository authentication");
		$this->addOption("skip-author", null, InputOption::VALUE_NEGATABLE, "Skip the [migrate:author] command", false);
		// Support [migrate:author] options.
		$this->addOption("author-email", null, InputOption::VALUE_REQUIRED, "Email address used for the map", "@company.com");
		$this->addOption("author-output-file", null, InputOption::VALUE_REQUIRED, "The name of the output file", "authors-file.txt");
		$this->addOption("author-override-file", null, InputOption::VALUE_NEGATABLE, "If there is a file that exists override it instead of throwing an error", false);
		// Support [migrate:clone] options.
		$this->addOption("trunk", null, InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/trunk");
		$this->addOption("tags", null, InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/tags");
		$this->addOption("branches", null, InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/branches");
		$this->addOption("include-metadata", null, InputOption::VALUE_NEGATABLE, "Includes the git-svn-id, can take significantly longer", false);
		$this->addOption("prefix", null, InputOption::VALUE_REQUIRED, "The prefix which is prepended to the names of remotes");

		$this->addUsage("https://repositoryhostprovider.com/svn/project");
		$this->addUsage("--username=diffusername https://repositoryhostprovider.com/svn/project");
		$this->addUsage("--skip-author --author-output-file=path/filename https://repositoryhostprovider.com/svn/project");
		$this->addUsage("--author-override-file https://repositoryhostprovider.com/svn/project");
		$this->addUsage("--include-metadata https://repositoryhostprovider.com/svn/project");
		$this->addUsage("--prefix=/ --trunk=/something --tags=/something2 --branches=/something3 https://repositoryhostprovider.com/svn/project");

		$this->setAliases(["migrate"]);
    }

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return void
	 * @throws Exception
	 */
	private function executeSvnCreateGitAuthorFileCommand(InputInterface $input, OutputInterface $output): void {
		$arrayInput = [
			"command" 		  => SvnCreateGitAuthorFileCommand::getDefaultName(),
			"svn-repo-url" 	  => $input->getArgument("svn-repo-url"),
			"--email"  	  	  => $input->getOption("author-email"),
			"--output-file"   => $input->getOption("author-output-file"),
			"--override-file" => $input->getOption("author-override-file")
		];

		if (!empty($input->getOption("username")))
			$arrayInput["--username"] = $input->getOption("username");

		$this->executeSubCommand(new ArrayInput($arrayInput), $output);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return void
	 * @throws Exception
	 */
	private function executeGitSvnCloneCommand(InputInterface $input, OutputInterface $output): void {
		$arrayInput = [
			"command"        	 => GitSvnCloneCommand::getDefaultName(),
			"svn-repo-url"   	 => $input->getArgument("svn-repo-url"),
			"--author-file" 	 => $input->getOption("author-output-file"),
			"--trunk"        	 => $input->getOption("trunk"),
			"--tags"         	 => $input->getOption("tags"),
			"--branches"     	 => $input->getOption("branches"),
			"--include-metadata" => $input->getOption("include-metadata"),
		];

		if (!empty($input->getOption("username")))
			$arrayInput["--username"] = $input->getOption("username");

		if (!empty($input->getOption("prefix")))
			$arrayInput["--prefix"] = $input->getOption("prefix");

		$this->executeSubCommand(new ArrayInput($arrayInput), $output);
	}

	/**
	 * Executes a sub-command.
	 * @param ArrayInput $input
	 * @param OutputInterface $output
	 * @return void
	 * @throws Exception
	 */
	private function executeSubCommand(ArrayInput $input, OutputInterface $output): void {
		try {
			$this->getApplication()
				->doRun($input, $output);
		} catch (Throwable $e) {
			throw new Exception(sprintf(
				"The %s command failed.",
				$input->getParameterOption("command"),
			), $e->getCode(), $e);
		}
	}
}

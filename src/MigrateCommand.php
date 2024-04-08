<?php

namespace SvnMigrate;

use Exception;
use SvnMigrate\Migrate\CloneCommand;
use SvnMigrate\Migrate\AuthorCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Throwable;

final class MigrateCommand extends Command {

	public const VERSION = "1.0.0";

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = "migrate";

    /**
     * {@inheritdoc}
     */
    protected static $defaultDescription = "Executes all migration sub commands";

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
		if (!$input->getOption("skip-author"))
			$this->executeSvnCreateGitAuthorFileCommand($input, $output);
		else
			$output->writeln("--skip-author flag found, skipping [" . AuthorCommand::getDefaultName() . "] command.");

		$this->promptAuthorFileLooksCorrect($input, $output);
		$this->executeGitSvnCloneCommand($input, $output);

        return self::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
		// Global options.
		$this->addArgument("svn-repo-url", InputArgument::REQUIRED, "The svn repository url to clone");
		$this->addOption("username", "u", InputOption::VALUE_REQUIRED, "Username for the svn repository authentication");

		// Support [migrate] options.
		$this->addOption("skip-author", null, InputOption::VALUE_NEGATABLE, "Skip the [migrate:author] command", false);

		// Support [author] options.
		$this->addOption("author-email", null, InputOption::VALUE_REQUIRED, "Email address used for the map", "@company.com");
		$this->addOption("author-output-file", null, InputOption::VALUE_REQUIRED, "The name of the output file", "authors-file.txt");
		$this->addOption("author-override-file", null, InputOption::VALUE_NEGATABLE, "If there is a file that exists override it instead of throwing an error", false);

		// Support [clone] options.
		$this->addArgument("output-dest", InputArgument::OPTIONAL, "The output destination for the contents of the clone");
		$this->addOption("trunk", null, InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/trunk");
		$this->addOption("tags", null, InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/tags");
		$this->addOption("branches", null, InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/branches");
		$this->addOption("include-metadata", null, InputOption::VALUE_NEGATABLE, "Includes the git-svn-id, can take significantly longer", false);
		$this->addOption("prefix", null, InputOption::VALUE_REQUIRED, "The prefix which is prepended to the names of remotes");
    }

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return void
	 */
	private function promptAuthorFileLooksCorrect(InputInterface $input, OutputInterface $output): void {
		if (!$this->getHelper("question")->ask(
			$input,
			$output,
			new ConfirmationQuestion(
				"Would you like review the author file \"{$input->getOption("author-output-file")}\" before continuing? (y/n): ",
				false,
			),
		)) return;

		$this->getHelper("question")->ask(
			$input,
			$output,
			new Question(
				"Press any key when ready to continue: ",
				"",
			),
		);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return void
	 * @throws Exception
	 */
	private function executeSvnCreateGitAuthorFileCommand(InputInterface $input, OutputInterface $output): void {
		$arrayInput = [
			"command" 		  => AuthorCommand::getDefaultName(),
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
			"command"        	 => CloneCommand::getDefaultName(),
			"svn-repo-url"   	 => $input->getArgument("svn-repo-url"),
			"output-dest"        => $input->getArgument("output-dest"),
			"--trunk"        	 => $input->getOption("trunk"),
			"--tags"         	 => $input->getOption("tags"),
			"--branches"     	 => $input->getOption("branches"),
			"--include-metadata" => $input->getOption("include-metadata"),
		];

		if (!empty($input->getOption("username")))
			$arrayInput["--username"] = $input->getOption("username");

		if (file_exists($input->getOption("author-output-file")))
			$arrayInput["--author-file"] = $input->getOption("author-output-file");

		if (!is_null($input->getOption("prefix")))
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
			$this->getApplication()->doRun($input, $output);
		} catch (Throwable $e) {
			throw new Exception(sprintf(
				"The %s command failed.",
				$input->getParameterOption("command"),
			), $e->getCode(), $e);
		}
	}
}

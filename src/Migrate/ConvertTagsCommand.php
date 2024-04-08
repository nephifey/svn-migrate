<?php

namespace SvnMigrate\Migrate;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class ConvertTagsCommand extends Command {

	/**
	 * {@inheritdoc}
	 */
	protected static $defaultName = "migrate:convert-tags";

	/**
	 * {@inheritdoc}
	 */
	protected static $defaultDescription = "Uses git to read remote tags, convert them to git tags, and delete the remotes";

	/**
	 * {@inheritdoc}
	 * @throws Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$process = $this->buildRemotesProcess($input);
		$exitCode = $process->run();

		if (!$process->isSuccessful())
			throw new ProcessFailedException($process);

		// TODO: Tag conversion...

		return $exitCode;
	}

	/**
	 * {@inheritdoc}
	 * @throws Exception
	 */
	protected function initialize(InputInterface $input, OutputInterface $output) {
		if (!empty($input->getArgument("cwd")) && !file_exists($input->getArgument("cwd")))
			throw new Exception(sprintf(
				"The cwd \"%s\" does not exist.",
				$input->getArgument("cwd"),
			));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure() {
		$this->addArgument("cwd", InputArgument::REQUIRED, "The cwd of the git-svn clone");
	}

	/**
	 * @param InputInterface $input
	 * @return Process
	 */
	protected function buildRemotesProcess(InputInterface $input): Process {
		$cmd = 'git for-each-ref --format="${:FORMAT}" refs/remotes/tags';
		$args = ["FORMAT" => "%(refname:short)"];

		return Process::fromShellCommandline($cmd, $input->getArgument("cwd"), $args, null, null);
	}
}
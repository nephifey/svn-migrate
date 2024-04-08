<?php

namespace SvnMigrate\Migrate;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class ConvertBranchesCommand extends Command {

	/**
	 * {@inheritdoc}
	 */
	protected static $defaultName = "migrate:convert-branches";

	/**
	 * {@inheritdoc}
	 */
	protected static $defaultDescription = "Uses git to read remote branches, convert them to git branches, and delete the remotes";

	/**
	 * {@inheritdoc}
	 * @throws Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$process = $this->buildRemotesProcess($input);
		$exitCode = $process->run();

		if (!$process->isSuccessful())
			throw new ProcessFailedException($process);

		$this->convert($process, $input, $output);

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
		$cmd = 'git for-each-ref --format="${:FORMAT}" refs/remotes';
		$args = ["FORMAT" => "%(refname:short)"];

		return Process::fromShellCommandline($cmd, $input->getArgument("cwd"), $args, null, null);
	}

	/**
	 * @param InputInterface $input
	 * @param string $remote
	 * @return Process
	 */
	 protected function buildRemoteToLocalProcess(InputInterface $input, string $remote): Process {
		$cmd = 'git branch "${:REMOTE}" refs/remotes/"${:REMOTE}"';
		$args = ["REMOTE" => $remote];

		return Process::fromShellCommandline($cmd, $input->getArgument("cwd"), $args, null, null);
	 }

	/**
	 * @param InputInterface $input
	 * @param string $remote
	 * @return Process
	 */
	protected function buildDeleteRemoteProcess(InputInterface $input, string $remote): Process {
		$cmd = 'git branch -D -r "${:REMOTE}"';
		$args = ["REMOTE" => $remote];

		return Process::fromShellCommandline($cmd, $input->getArgument("cwd"), $args, null, null);
	}

	/**
	 * @param Process $process
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return void
	 * @throws ProcessFailedException
	 */
	protected function convert(Process $process, InputInterface $input, OutputInterface $output): void {
		if (empty(($processOutput = $process->getOutput())))
			return;

		foreach (explode(PHP_EOL, $processOutput) as $branch) {
			$rtlProcess = $this->buildRemoteToLocalProcess($input, ($branch = trim($branch)));
			$rtlProcess->run();

			if (!$rtlProcess->isSuccessful())
				throw new ProcessFailedException($rtlProcess);
			else if (!empty(($rtlProcessOutput = $rtlProcess->getOutput())))
				$output->write($rtlProcessOutput);

			$drProcess = $this->buildDeleteRemoteProcess($input, $branch);
			$drProcess->run();

			if (!$drProcess->isSuccessful())
				throw new ProcessFailedException($drProcess);
			else if (!empty(($drProcessOutput = $drProcess->getOutput())))
				$output->write($drProcessOutput);
		}
	}
}
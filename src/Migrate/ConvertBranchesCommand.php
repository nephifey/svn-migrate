<?php

namespace SvnMigrate\Migrate;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class ConvertBranchesCommand extends Command {

    use ConvertHelperTrait;

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
        $this->addOption("prefix", null, InputOption::VALUE_REQUIRED, "The prefix which is prepended to the names of remotes", "origin/");
	}

	/**
	 * @param InputInterface $input
	 * @return Process
	 */
	protected function buildRemotesProcess(InputInterface $input): Process {
		$args = [
            "FORMAT" => "%(refname:short)",
            "PREFIX" => $input->getOption("prefix"),
        ];

		return Process::fromShellCommandline(
            'git for-each-ref --format="${:FORMAT}" refs/remotes/"${:PREFIX}"',
            $input->getArgument("cwd"),
            $args,
            null,
            null
        );
	}

	/**
	 * @param InputInterface $input
	 * @param string $remote
	 * @return Process
	 */
	 protected function buildRemoteToLocalProcess(InputInterface $input, string $remote): Process {
		return Process::fromShellCommandline(
            'git branch "${:REMOTE}" refs/remotes/"${:REMOTE}"',
            $input->getArgument("cwd"),
            ["REMOTE" => $remote],
            null,
            null
        );
	 }

	/**
	 * @param InputInterface $input
	 * @param string $remote
	 * @return Process
	 */
	protected function buildDeleteRemoteProcess(InputInterface $input, string $remote): Process {
		return Process::fromShellCommandline(
            'git branch -D -r "${:REMOTE}"',
            $input->getArgument("cwd"),
            ["REMOTE" => $remote],
            null,
            null
        );
	}
}

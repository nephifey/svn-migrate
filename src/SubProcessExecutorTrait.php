<?php

namespace SvnMigrate;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait SubProcessExecutorTrait {

	/**
	 * @var Process Holds the sub-process.
	 */
	protected Process $subProcess;

	/**
	 * @var InputInterface Current command input.
	 */
	protected InputInterface $input;

	/**
	 * @var OutputInterface Current command output.
	 */
	protected OutputInterface $output;

	/**
	 * Builds the sub-process.
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return Process
	 */
	abstract protected function buildSubProcess(InputInterface $input, OutputInterface $output): Process;

	/**
	 * @param string $type Output available on STDOUT or STDERR from sub-process.
	 * @param string $data The output data to write.
	 * @return void
	 */
	public function subProcessOutToCommandOut(string $type, string $data): void {
		$this->output->write($data);
	}

	/**
	 * Executes the sub-process.
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param callable|null $callback A PHP callback to run whenever there is some output available on STDOUT or STDERR.
	 * @param array $env Additional arguments to supply the sub-process.
	 * @return int
	 */
	protected function executeSubProcess(InputInterface $input, OutputInterface $output, ?callable $callback = null, array $env = []): int {
		$this->input = $input;
		$this->output = $output;
		$this->subProcess = $this->buildSubProcess($this->input, $this->output);

		$exitCode = $this->subProcess->run($callback, $env);

		if (!$this->subProcess->isSuccessful())
			throw new ProcessFailedException($this->subProcess);

		return $exitCode;
	}
}
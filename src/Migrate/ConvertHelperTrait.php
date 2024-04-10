<?php

namespace SvnMigrate\Migrate;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait ConvertHelperTrait {

    /**
     * Builds the process for fetching remote refs.
     * @param InputInterface $input
     * @return Process
     */
    abstract protected function buildRemotesProcess(InputInterface $input): Process;

    /**
     * Builds the process for the remote ref to local.
     * @param InputInterface $input
     * @param string $remote
     * @return Process
     */
    abstract protected function buildRemoteToLocalProcess(InputInterface $input, string $remote): Process;

    /**
     * Builds the process for the remote ref deletion.
     * @param InputInterface $input
     * @param string $remote
     * @return Process
     */
    abstract protected function buildDeleteRemoteProcess(InputInterface $input, string $remote): Process;

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
     * @param Process $process
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws ProcessFailedException
     */
    protected function convert(Process $process, InputInterface $input, OutputInterface $output): void {
        if (empty(($processOutput = $process->getOutput())))
            return;

        $processOutput = str_replace(["\r\n", "\n"], "\n", $processOutput);

        foreach (explode("\n", $processOutput) as $remote) {
            if (empty(($remote = trim($remote))))
                continue;

            $rtlProcess = $this->buildRemoteToLocalProcess($input, $remote);
            $rtlProcess->run();

            if (!$rtlProcess->isSuccessful())
                throw new ProcessFailedException($rtlProcess);
            else if (!empty(($rtlProcessOutput = $rtlProcess->getOutput())))
                $output->write($rtlProcessOutput);

            $drProcess = $this->buildDeleteRemoteProcess($input, $remote);
            $drProcess->run();

            if (!$drProcess->isSuccessful())
                throw new ProcessFailedException($drProcess);
            else if (!empty(($drProcessOutput = $drProcess->getOutput())))
                $output->write($drProcessOutput);
        }
    }
}

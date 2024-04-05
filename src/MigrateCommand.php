<?php

namespace SvnMigrate;

use Exception;
use SvnMigrate\MigrateCore\GitSvnCloneCommand;
use SvnMigrate\MigrateCore\SvnCreateGitAuthorsFileCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class MigrateCommand extends Command {

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
        $this->executeSvnCreateGitAuthorsFileCommand($input, $output);
        $this->executeGitSvnCloneCommand($input, $output);

        return self::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->addArgument("svn-repo-url", InputArgument::REQUIRED, "The svn repository url to clone");
        $this->addOption("username", "u", InputOption::VALUE_REQUIRED, "Username for the svn repository authentication");
        $this->setAliases(["migrate"]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function executeSvnCreateGitAuthorsFileCommand(InputInterface $input, OutputInterface $output): void {
        $commandInput = new ArrayInput([
            "command" => SvnCreateGitAuthorsFileCommand::getDefaultName(),
            "svn-repo-url" => $input->getArgument("svn-repo-url"),
            "--override-file" => true,
        ]);

        try {
            $this->getApplication()
                ->doRun($commandInput, $output);
        } catch (Throwable $e) {
            throw new Exception(sprintf(
                "The %s failed.",
                SvnCreateGitAuthorsFileCommand::class,
            ), $e->getCode(), $e);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function executeGitSvnCloneCommand(InputInterface $input, OutputInterface $output): void {
        $commandInput = new ArrayInput([
            "command" => GitSvnCloneCommand::getDefaultName(),
            "svn-repo-url" => $input->getArgument("svn-repo-url"),
            "--authors-file" => "authors-file.txt",
        ]);

        try {
            $this->getApplication()
                ->doRun($commandInput, $output);
        } catch (Throwable $e) {
            throw new Exception(sprintf(
                "The %s failed.",
                GitSvnCloneCommand::class,
            ), $e->getCode(), $e);
        }
    }
}

<?php

namespace SvnMigrate\MigrateCore;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

class GitSvnCloneCommand extends Command {

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = "migrate:git-svn-clone";

    /**
     * {@inheritdoc}
     */
    protected static $defaultDescription = "Uses git-svn to clone a svn repository into a git repository";

    /**
     * @var Process Git svn clone process.
     */
    private Process $process;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->buildGitSvnCloneCommand($input);
        $exitCode = $this->process->run();

        if (!$this->process->isSuccessful())
            throw new ProcessFailedException($this->process);

        return 0 === $exitCode
            ? self::SUCCESS
            : self::FAILURE;
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

        $violations = $validator->validate($input->getOption("authors-file"), [new Optional(), new File()]);
        if (0 !== count($violations))
            throw new Exception($violations[0]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->addArgument("svn-repo-url", InputArgument::REQUIRED, "The svn repository url to clone");
        $this->addArgument("output-dest", InputArgument::OPTIONAL, "The output destination for the contents of the clone");
        $this->addOption("username", "u", InputOption::VALUE_REQUIRED, "Username for the svn repository authentication");
        $this->addOption("trunk", "T", InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/trunk");
        $this->addOption("tags", "t", InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/tags");
        $this->addOption("branches", "b", InputOption::VALUE_REQUIRED, "The svn repository trunk path", "/branches");
        $this->addOption("authors-file", null, InputOption::VALUE_REQUIRED, "The authors file to use for mapping to Git");
        $this->addOption("include-metadata", null, InputOption::VALUE_NEGATABLE, "Includes the git-svn-id, can take significantly longer", false);
        $this->addOption("prefix", null, InputOption::VALUE_REQUIRED, "The prefix which is prepended to the names of remotes");
        $this->setAliases(["migrate:clone"]);
    }

    /**
     * @param InputInterface $input
     * @return void
     */
    protected function buildGitSvnCloneCommand(InputInterface $input): void {
        $cmd = 'git svn clone "${:SVN_REPO_URL}" --trunk="${:TRUNK}" --tags="${:TAGS}" --branches="${:BRANCHES}"';
        $args = [
            "SVN_REPO_URL" => $input->getArgument("svn-repo-url"),
            "TRUNK"        => $input->getOption("trunk"),
            "TAGS"         => $input->getOption("tags"),
            "BRANCHES"     => $input->getOption("branches"),
        ];

        if (!$input->getOption("include-metadata")) {
            $cmd .= ' --no-metadata';
        }

        if (!empty($input->getOption("authors-file"))) {
            $args["AUTHORS_FILE"] = $input->getOption("authors-file");
            $cmd .= ' --authors-file="${:AUTHORS_FILE}"';
        }

        if (!empty($input->getOption("prefix"))) {
            $args["PREFIX"] = $input->getOption("prefix");
            $cmd .= ' --prefix="${:PREFIX}"';
        }

        if (!empty($input->getOption("username"))) {
            $args["USERNAME"] = $input->getOption("username");
            $cmd .= ' --username="${:USERNAME}"';
        }

        if (!empty($input->getArgument("output-dest"))) {
            $args["OUTPUT_DEST"] = $input->getArgument("output-dest");
            $cmd .= ' "${:OUTPUT_DEST}"';
        }

        $this->process = Process::fromShellCommandline($cmd, null, $args, null, null);
    }
}

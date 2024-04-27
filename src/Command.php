<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate;

use Nephifey\SvnMigrate\Exception\MigrateException;
use Nephifey\SvnMigrate\Exception\SystemRequirementsException;
use Nephifey\SvnMigrate\Migrate\Migrate;
use Nephifey\SvnMigrate\Setup\Question\QuestionList;
use Nephifey\SvnMigrate\Setup\SystemRequirements;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Command extends \Symfony\Component\Console\Command\Command {

    protected static $defaultName = "svn-migrate";

    protected static $defaultDescription = "Migration tool to convert SVN repositories into Git repositories.";

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $cli = new CommandStyle($input, $output);
        /* @phpstan-ignore-next-line */
        $cli->title(self::$defaultDescription);

        try {
            SystemRequirements::check($cli);
            $migrate = new Migrate(QuestionList::askQuestions($cli), $cli);
            $migrate->runProcesses();

            $cli->success([
                "Migration is finished!",
                "The git repository can be found in '{$migrate->getAnswers()->getOutputDestination()}.'",
            ]);

            return self::SUCCESS;
        } catch (SystemRequirementsException $exception) {
            $cli->error("There was an error while checking system requirements: {$exception->getProcess()->getCommandLine()}.");
        } catch (ReflectionException $exception) {
            $cli->error("There was an error while asking questions.");
        } catch (MigrateException $exception) {
            $cli->error("There was an error while migrating: {$exception->getMessage()}");
        }

        return self::FAILURE;
    }
}

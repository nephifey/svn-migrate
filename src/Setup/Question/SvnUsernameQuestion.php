<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate\Setup\Question;

use Closure;
use InvalidArgumentException;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SvnUsernameQuestion extends Question {

    private const SVN_AUTH_REGEX = "/Username:\s*(.*)/";

    public function __construct(string $question = "What is your SVN username to use for authentication?", $default = null) {
        parent::__construct($question, $default);
    }

    public function getValidator(): Closure {
        return static function (?string $username) {
            if (empty($username)) {
                throw new InvalidArgumentException("The SVN username is required.");
            }

            return $username;
        };
    }

    public function getDefault() {
        try {
            $process = new Process(["svn", "auth"]);
            $process->mustRun();

            if (preg_match(self::SVN_AUTH_REGEX, $process->getOutput(), $matches)) {
                return trim($matches[1]);
            }
        } catch (ProcessFailedException $exception) {}

        return null;
    }
}

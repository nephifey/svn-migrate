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
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

final class SvnRepositoryUrlQuestion extends Question {

    public function __construct(string $question = "What is the SVN repository url?", $default = null) {
        parent::__construct($question, $default);
    }

    public function getValidator(): Closure {
        return static function (?string $svnRepositoryUrl): string {
            if (empty($svnRepositoryUrl)) {
                throw new InvalidArgumentException("The SVN repository url is required.");
            }

            $violations = (Validation::createValidator())
                ->validate($svnRepositoryUrl, new Url());

            if (count($violations) > 0) {
                throw new InvalidArgumentException("The SVN repository url is invalid.");
            }

            return $svnRepositoryUrl;
        };
    }
}

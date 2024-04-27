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

use Symfony\Component\Console\Question\Question;

final class OutputDestinationQuestion extends Question implements SetupQuestionInterface {

    use SetupQuestionTrait;

    public function __construct(string $question = "Where do you want the Git repository to be output?", $default = null) {
        parent::__construct($question, $default);
    }

    public function getDefault() {
        if (!isset($this->answers) || empty($this->answers->getSvnRepositoryUrl())) {
            return null;
        }

        $svnRepositoryUrlExploded = explode("/", $this->answers->getSvnRepositoryUrl());

        return end($svnRepositoryUrlExploded);
    }
}

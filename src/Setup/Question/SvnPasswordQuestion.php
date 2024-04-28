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

final class SvnPasswordQuestion extends Question {

    public function __construct(string $question = "What is your SVN password to use for authentication?", $default = null) {
        parent::__construct($question, $default);
        $this->setHidden(true);
    }
}
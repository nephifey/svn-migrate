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

final class SvnTagsQuestion extends Question {

    public function __construct(string $question = "What is the SVN repository 'tags' path?", $default = null) {
        parent::__construct($question, $default);
    }
}

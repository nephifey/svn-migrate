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

use Nephifey\SvnMigrate\Setup\Answers;

interface SetupQuestionInterface {

    public function setAnswers(Answers $answers): void;
}

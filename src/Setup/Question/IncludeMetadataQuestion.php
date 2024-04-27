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

use Symfony\Component\Console\Question\ConfirmationQuestion;

final class IncludeMetadataQuestion extends ConfirmationQuestion {

    public function __construct(string $question = "Do you want to include metadata (git-svn-id)?", $default = false) {
        parent::__construct($question, $default);
    }
}

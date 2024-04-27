<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate\Migrate\Process;

use Nephifey\SvnMigrate\Migrate\Migrate;

abstract class AbstractProcess implements MigrateProcessInterface {

    protected Migrate $migrate;

    public function __construct(Migrate $migrate) {
        $this->migrate = $migrate;
    }
}

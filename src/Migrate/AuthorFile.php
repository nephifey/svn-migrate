<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate\Migrate;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class AuthorFile {

    private Filesystem $filesystem;

    private string $name;

    /**
     * @var resource|false
     */
    private $stream = false;

    /**
     * @throws IOException
     */
    public function __construct() {
        $this->filesystem = new Filesystem();
        $this->name = $this->filesystem->tempnam(sys_get_temp_dir(), "author-file");
    }

    public function __destruct() {
        $this->unlink();
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @return resource|false
     */
    public function getStream() {
        if (false === $this->stream) {
            $this->stream = fopen($this->name, "w+");
        }

        return $this->stream;
    }

    public function closeStream(): void {
        if (is_resource($this->stream)) {
            fclose($this->stream);
            $this->stream = false;
        }
    }

    public function unlink(): void {
        $this->closeStream();

        if ($this->exists()) {
            try {
                $this->filesystem->remove($this->name);
            } catch (IOException $exception) {}
        }
    }

    public function exists(): bool {
        try {
            return $this->filesystem->exists($this->name);
        } catch (IOException $exception) {
            return false;
        }
    }

    /**
     * @param string $data
     * @param int|null $length
     * @return int|false
     */
    public function write(string $data, ?int $length = null) {
        $stream = $this->getStream();
        if (false === $stream) {
            return false;
        }

        /* @phpstan-ignore-next-line */
        return fwrite($stream, $data, $length);
    }

    /**
     * @return false|string
     */
    public function getContents() {
        return file_get_contents($this->name);
    }
}

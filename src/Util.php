<?php

namespace SvnMigrate;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class Util {

    /**
     * Deletes a directory and all the contents within it.
     * @param string $path The directory to delete.
     * @return bool
     */
    public static function rmDirRecursive(string $path): bool {
        if (!file_exists($path))
            return true;

        $callback = function (SplFileInfo $splFileInfo) {
            @chmod($splFileInfo, 0777);

            if ($splFileInfo->isFile())
                @unlink($splFileInfo);
            else if ($splFileInfo->isDir())
                @rmdir($splFileInfo);
        };

        self::recurseDir(
            $path,
            $callback,
            FilesystemIterator::SKIP_DOTS,
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        @chmod($path, 0777);
        return @rmdir($path);
    }

    /**
     * Recurse a directory and send a callback to a user-defined callable.
     * @param string $path The directory to recurse.
     * @param callable $callback The callback function to provide $splFileInfo.
     * @param int $rdiFlags RecursiveDirectoryIterator flags.
     * @param int $riiFlags RecursiveIteratorIterator flags.
     * @return void
     */
    public static function recurseDir(string $path, callable $callback, int $rdiFlags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO, int $riiFlags = 0): void {
        if (!file_exists($path))
            return;

        /**
         * @var SplFileInfo $splFileInfo
         */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, $rdiFlags), $riiFlags
         ) as $splFileInfo)
            $callback($splFileInfo);
    }

    /**
     * Checks a directory to see if there is anything in it.
     * @param string $path The directory to check.
     * @return bool
     */
    public static function isDirEmpty(string $path): bool {
        if (!file_exists($path))
            return true;

        return !(
            new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS)
        )->valid();
    }
}

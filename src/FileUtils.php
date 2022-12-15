<?php

namespace parseword\pickset;

/**
 * The FileUtils class contains various methods for interacting with files
 * and directories.
 *
 * *****************************************************************************
 * This file is part of pickset, a collection of PHP utilities.
 *
 * Copyright 2012, 2022 Shaun Cummiskey <shaun@shaunc.com> <https://shaunc.com/>
 * and additional contributors or muses where indicated.
 *
 * Repository: <https://github.com/parseword/pickset/>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class FileUtils
{

    /**
     * Return an array of arrays containing metadata about the files in the
     * specified directory. For each file, the following are provided: the
     * fully-qualified path, size in bytes, epoch atime, epoch ctime, epoch
     * mtime, octal permissions mode, owner UID, and integer permissions mode.
     *
     * @param string $directory The target directory path
     * @param bool $recursive Whether or not to recurse into subdirectories
     * @param array|null $exts If set, only find files with these extensions.
     *      Do not include dots, e.g.:   ['txt', 'jpg', 'sql']
     * @return array An array of arrays of file metadata
     */
    public static function getDirectoryContents(string $directory,
            bool $recursive = false, ?array $exts = []): array {

        $files = [];

        // Add path separator if needed
        if (false === strpos($directory, '/', -1)) {
            $directory = $directory . '/';
        }

        // Bail if the target can't be opened
        if (!(is_dir($directory) && $handle = opendir($directory))) {
            return $files;
        }

        // Loop over the directory, adding files to the array
        while (false !== ($filename = readdir($handle))) {

            // Skip this directory and the parent directory
            if ($filename === '.' || $filename === '..') {
                continue;
            }

            $path = $directory . $filename;

            // If extensions were provided and this file doesn't match, skip it
            if (!empty($exts) && !in_array(pathinfo($path, PATHINFO_EXTENSION),
                            $exts)) {
                continue;
            }

            // If this is a file, add it to the array
            $type = filetype($path);
            if ('file' === $type) {
                $files[] = [
                    'name'  => $path,
                    'size'  => filesize($path),
                    'atime' => fileatime($path),
                    'ctime' => filectime($path),
                    'mtime' => filemtime($path),
                    'octal' => decoct(fileperms($path)),
                    'owner' => fileowner($path),
                    'perms' => fileperms($path),
                ];
                continue;
            }

            // Determine whether recursion is needed
            if ('dir' === $type && $recursive) {
                $files = array_merge($files,
                        self::getDirectoryContents($path, $recursive));
            }
        }
        closedir($handle);
        return $files;
    }

    /**
     * Return an array of file paths representing the contents of the target
     * directory, ordered by date instead of by filename.
     *
     * @param string $path The target directory path
     * @param bool $reverse Whether to sort in reverse date order (oldest first)
     * @param array|null $exts If set, only find files with these extensions.
     *      Do not include dots, e.g.:   ['txt', 'jpg', 'sql']
     * @return array A sorted array of absolute filesystem paths
     */
    public static function scandir_chrono(string $path, bool $reverse = false,
            ?array $exts = []): array {

        // Fail if the directory can't be opened
        if (!(is_dir($path) && $dir = opendir($path))) {
            return [];
        }

        $files = [];

        while (($file = readdir($dir)) !== false) {
            // Skip anything that's not a regular file
            if ('file' !== filetype($path . '/' . $file)) {
                continue;
            }
            // If extensions were provided and this file doesn't match, skip it
            if (!empty($exts) && !in_array(pathinfo($path . '/' . $file,
                                    PATHINFO_EXTENSION), $exts)) {
                continue;
            }
            // Add this file to the array with its modification time as the key
            $files[filemtime($path . '/' . $file)] = $file;
        }
        closedir($dir);

        // Sort and return the array
        $fn = $reverse ? 'krsort' : 'ksort';
        $fn($files);
        return $files;
    }

}

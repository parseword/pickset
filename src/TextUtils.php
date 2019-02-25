<?php

namespace parseword\pickset;

/**
 * The TextUtils class contains various methods for parsing and manipulating
 * text strings.
 *
 * *****************************************************************************
 * This file is part of pickset, a collection of PHP utilities.
 *
 * Copyright 2012, 2019 Shaun Cummiskey <shaun@shaunc.com> <https://shaunc.com/>
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
class TextUtils
{

    /**
     * Convert a byte count into a human-friendly approximate size. For example,
     * 473832 becomes "462.73KB". Uses powers of 1024 by default. Adapted from
     * http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
     *
     * @param int $bytes The number of bytes
     * @param int $precision The precision to use in the returned value
     * @param int $power The power based on which sizes are calculated
     * @return string
     */
    public static function bytesToHuman(int $bytes, int $precision = 2,
            int $power = 1024): string {

        if (!in_array($power, [1024, 1000])) {
            return 'Invalid power specified; use 1024 or 1000.';
        }
        $factor = floor((strlen((string) $bytes) - 1) / 3);
        return sprintf("%.{$precision}f", $bytes / pow($power, $factor))
                . ['B', 'KB', 'MB', 'GB', 'TB', 'PB'][$factor];
    }

    /**
     * Extract IPv4 CIDR masks from the supplied text and return them as an
     * array. Supports fully-qualified masks only: 10.0.0.0/8 will be matched,
     * but 10/8 or 10.0/16 will not.
     *
     * @param string $input
     * @return array
     */
    public static function extractCidrs(string $input): array {
        if (empty(preg_match_all('|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2}|mi',
                                $input, $matches))) {
            return [];
        }
        return $matches[0];
    }

    /**
     * Extract IPv6 CIDR masks from the supplied text and return them as an
     * array.
     *
     * @param string $input
     * @return array
     * @see http://home.deds.nl/~aeron/regex/
     */
    public static function extractCidrs6(string $input): array {
        if (empty(preg_match_all('/(((?=.*(::))(?!.*\3.+\3))\3?|([\dA-F]{1,4}(\3|:\b|$)|\2))(?4){5}((?4){2}|(((2[0-4]|1\d|[1-9])?\d|25[0-5])\.?\b){4})\/\d{1,2}/mi',
                                $input, $matches))) {
            return [];
        }
        return $matches[0];
    }

    /**
     * Extract IPv4 addresses from the supplied text and return them as an array.
     *
     * @param string $input
     * @return array
     */
    public static function extractIps(string $input): array {
        if (empty(preg_match_all('|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|mi',
                                $input, $matches))) {
            return [];
        }
        return $matches[0];
    }

    /**
     * Extract IPv6 addresses from the supplied text and return them as an array.
     *
     * @param string $input
     * @return array
     * @see http://home.deds.nl/~aeron/regex/
     */
    public static function extractIps6(string $input): array {
        if (empty(preg_match_all('/(((?=.*(::))(?!.*\3.+\3))\3?|([\dA-F]{1,4}(\3|:\b|$)|\2))(?4){5}((?4){2}|(((2[0-4]|1\d|[1-9])?\d|25[0-5])\.?\b){4})/mi',
                                $input, $matches))) {
            return [];
        }
        return $matches[0];
    }

    /**
     * Returns a statistically unique identifier consisting of the current date
     * and time parts with a random 16-character string appended. The returned
     * identifier is 32 characters long. Pass $gmt = true to use GMT.
     *
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @return string A generated ID like 2015-0822195015-715ae8536c9ef3c5
     */
    public static function generateId(bool $gmt = false): string {
        $function = $gmt ? 'gmdate' : 'date';
        return $function('Y-mdHis-') . bin2hex(random_bytes(8));
    }

    /**
     * Remove blank lines and comments from an array of strings.
     *
     * If a line is empty, contains only whitespace, or begins with $delimiter,
     * the entire line is removed. If $delimiter is found within a line, the
     * $delimiter and the rest of that line (an inline comment) are removed.
     * trim() is called on all remaining elements before the array is returned.
     *
     * @param array $arr Array of strings to filter, e.g. the results of file()
     * @param string $delimiter The comment delimiter, e.g. '#' or '//'
     * @return array The filtered array with each line trimmed
     */
    public static function stripComments(array $arr, string $delimiter = '#') {
        /* Filter blank lines and lines that start with $delimiter */
        $arr = array_filter($arr,
                function($val) use ($delimiter) {
            return !((strpos($val, $delimiter) === 0) || (strlen(trim($val)) == 0));
        });
        /* Filter inline comments */
        foreach ($arr as $key => $val) {
            if (strpos($val, $delimiter) !== false) {
                $arr[$key] = strtok($val, $delimiter);
            }
        }
        return array_map('trim', $arr);
    }

    /**
     * Apply str_pad() to every line of a supplied string, or every element of
     * a supplied array, then return the modified string or array. Optionally
     * prepend and/or append a string to all elements, after padding has occurred.
     *
     * @param mixed $input A string or an array of strings
     * @param int $length The length to which each line should be padded
     * @param string $char The character with which to pad
     * @param string $prepend An optional string to prepend to each line after padding
     * @param string $append An optional string to append to each line after padding
     * @return type
     */
    public static function padAll($input, int $length, string $char = ' ',
            string $prepend = '', string $append = '') {
        if (!is_array($input)) {
            $string = true;
            $input = explode("\n", $input);
        }
        $input = array_map(function($val) use ($length, $char, $prepend, $append) {
            return $prepend . str_pad($val, $length, $char) . $append;
        }, $input);

        return isset($string) ? join("\n", $input) : $input;
    }

    public static function stripTrailingSlash(string $string): string {
        if (strrpos($string, '/') !== strlen($string) - 1) {
            return $string;
        }
        return substr($string, 0, strlen($string) - 1);
    }

}

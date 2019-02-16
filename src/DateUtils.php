<?php

namespace parseword\pickset;

/**
 * The DateUtils class contains various methods for converting and working with
 * DateTime objects and epoch timestamps.
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
class DateUtils
{

    /**
     * Convert a standard Apache log timestamp into its corresponding epoch.
     * For example, "19/May/2014:17:30:56 -0500" becomes 1400538656.
     *
     * @param string $apacheTimestamp
     * @return int An epoch timestamp
     */
    public static function apacheToEpoch(string $apacheTimestamp): int {
        list($d, $M, $y, $h, $i, $s, $z) = sscanf($apacheTimestamp,
                "%2d/%3s/%4d:%2d:%2d:%2d %5s");
        return strtotime("$d $M $y $h:$i:$s $z");
    }

    /**
     * Return a timestamp suitable for using in a Set-Cookie HTTP header,
     * corresponding to the given epoch. The format is as described in RFC6265,
     * which remains "Proposed" but is widely cited.
     *
     * @param int $epoch The epoch value
     * @return type A timestamp in the format "Mon, 19 May 2014 22:30:56 GMT"
     * @see https://tools.ietf.org/html/rfc6265
     */
    public static function epochToCookie(int $epoch) {
        return gmdate('D, d M Y H:i:s T', $epoch);
    }

    /**
     * Return an RFC822 (SMTP) compliant datetime for a given epoch. If no epoch
     * is provided, the current time is used. Pass $gmt = true to use GMT.
     *
     * @param int $epoch The epoch value
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @return string An RFC822 datetime, e.g. "Wed, 03 Feb 16 21:50:28 -0600"
     * @deprecated Use date('r') or gmdate('r') instead
     */
    public static function epochToRfc822(int $epoch = null, bool $gmt = false): string {

        /*
         * DateTime::createFromFormat() ignores the time zone parameter when
         * given an epoch; setTimeZone() must be called manually.
         */
        $tz = $gmt ? new \DateTimeZone('GMT') : new \DateTimeZone(date_default_timezone_get());
        $date = \DateTime::createFromFormat('U', $epoch ?? time(), $tz);
        $date->setTimeZone($tz);
        return $date->format(\DateTimeInterface::RFC822);
    }

    /**
     * Return an RFC3339 datetime for a given epoch. If no epoch is provided,
     * the current time is used. Pass $gmt = true to use GMT.
     *
     * @param int $epoch The epoch value
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @return string An RFC3339 datetime, e.g. "2012-06-16T13:20:19-05:00"
     */
    public static function epochToRfc3339(int $epoch = null, bool $gmt = false): string {

        /*
         * DateTime::createFromFormat() ignores the time zone parameter when
         * given an epoch; setTimeZone() must be called manually.
         */
        $tz = $gmt ? new \DateTimeZone('GMT') : new \DateTimeZone(date_default_timezone_get());
        $date = \DateTime::createFromFormat('U', $epoch ?? time(), $tz);
        $date->setTimeZone($tz);
        return $date->format(\DateTimeInterface::RFC3339);
    }

    /**
     * Return the epoch of the first second (00:00:00) of the day corresponding
     * to a given epoch. If no epoch is provided, the current date is used.
     * Pass $gmt = true to use GMT.
     *
     * @param int $epoch The epoch for which to find the first second of the day
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @return int An epoch timestamp
     */
    public static function firstSecondOfDay(int $epoch = null, bool $gmt = false) {
        $epoch = $epoch ?? time();
        $function = $gmt ? 'gmmktime' : 'mktime';
        return $function(0, 0, 0, date('n', $epoch), date('j', $epoch),
                date('Y', $epoch));
    }

    /**
     * Return the epoch of the first second (00:00:00) of the month corresponding
     * to a given epoch. If no epoch is provided, the current date is used.
     * Pass $gmt = true to use GMT.
     *
     * @param int $epoch The epoch for which to find the first second of the month
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @return int An epoch timestamp
     */
    public static function firstSecondOfMonth(int $epoch = null,
            bool $gmt = false) {
        $epoch = $epoch ?? time();
        $function = $gmt ? 'gmmktime' : 'mktime';
        return $function(0, 0, 0, date('n', $epoch), 1, date('Y', $epoch));
    }

    /**
     * Return the epoch of the first second (00:00:00) of the Monday prior to a
     * given epoch. If the epoch represents a Monday, the first second of that
     * same day is returned. If no epoch is provided, the current date is used.
     * Pass $gmt = true to use GMT.
     *
     * @param int $epoch The epoch for which to find the first second of the
     *      most recent Monday
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @return int An epoch timestamp
     */
    public static function firstSecondOfWeekMonday(int $epoch = null,
            bool $gmt = false) {
        $epoch = $epoch ?? time();
        return self::firstSecondOfDay($epoch - ((int) date('N', $epoch) - 1) * 86400,
                        $gmt);
    }

    /**
     * Return the epoch of the first second (00:00:00) of the Sunday prior to a
     * given epoch. If the epoch represents a Sunday, the first second of that
     * same day is returned. If no epoch is provided, the current date is used.
     * Pass $gmt = true to use GMT.
     *
     * @param int $epoch The epoch for which to find the first second of the
     *      most recent Sunday
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @return int An epoch timestamp
     */
    public static function firstSecondOfWeekSunday(int $epoch = null,
            bool $gmt = false) {
        $epoch = $epoch ?? time();
        return self::firstSecondOfDay($epoch - (int) (date('w', $epoch)) * 86400,
                        $gmt);
    }

    /**
     * Return the epoch of the first second (00:00:00) of the year corresponding
     * to a given epoch. If no epoch is provided, the current time is used.
     * Pass $gmt = true to use GMT.
     *
     * @param int $epoch The epoch for which to find the first second of the year
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @return int An epoch timestamp
     */
    public static function firstSecondOfYear(int $epoch = null,
            bool $gmt = false) {
        $epoch = $epoch ?? time();
        $function = $gmt ? 'gmmktime' : 'mktime';
        return $function(0, 0, 0, 1, 1, date('Y', $epoch));
    }

    /**
     * Convert a number of seconds into an array containing the corresponding
     * numbers of days, hours, minutes, and seconds. For example, 530345 becomes
     * ['days' => 6, 'hours' => 3, 'minutes' => 19, 'seconds' => 5]
     *
     * @param int $seconds
     * @return array
     */
    public static function secondsToDaypartsArray(int $seconds): array {

        $seconds = abs($seconds);

        return [
            'days'    => (int) floor($seconds / 86400),
            'hours'   => (int) floor(($seconds % 86400) / 3600),
            'minutes' => (int) floor((($seconds % 86400) % 3600) / 60),
            'seconds' => (int) floor((($seconds % 86400) % 3600) % 60)
        ];
    }

    /**
     * Convert a number of seconds into a human-friendly string of days, hours,
     * minutes, and seconds. For example, 530345 becomes "6d 3h 19m 5s"
     *
     * @param int $seconds
     * @return string
     */
    public static function secondsToDaypartsString(int $seconds): string {

        $dayparts = self::secondsToDaypartsArray($seconds);

        $string = '';
        if ($dayparts['days'] > 0) {
            $string .= "{$dayparts['days']}d ";
        }
        if ($dayparts['hours'] > 0) {
            $string .= "{$dayparts['hours']}h ";
        }
        if ($dayparts['minutes'] > 0) {
            $string .= "{$dayparts['minutes']}m ";
        }
        if ($dayparts['seconds'] > 0) {
            $string .= "{$dayparts['seconds']}s ";
        }

        return trim($string);
    }

    /**
     * Return a timestamp in the format specified by $dateFormat. The default
     * will generate a stamp like "2016-02-03,00:27:31.596 CST" using the
     * system's local timezone. Pass $gmt = true to use GMT.
     *
     * @param bool $gmt Whether to use GMT instead of the local system time
     * @param string $dateFormat A date format string
     * @return string
     */
    public static function stamp(bool $gmt = false,
            string $dateFormat = 'Y-m-d,H:i:s.v T'): string {
        $tz = $gmt ? new \DateTimeZone('GMT') : null;
        return (new \DateTime('now', $tz))->format($dateFormat);
    }

}

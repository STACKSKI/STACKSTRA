<?php

namespace Stackstra\Etc;

use Stackstra\Types\Floats;

use const Stackstra\BYTES_IN_GIGABYTE;
use const Stackstra\BYTES_IN_KILOBYTE;
use const Stackstra\BYTES_IN_MEGABYTE;
use const Stackstra\MILLISECONDS_IN_MICROSECOND;
use const Stackstra\SECONDS_IN_DAY;
use const Stackstra\SECONDS_IN_HOUR;
use const Stackstra\SECONDS_IN_MICROSECOND;
use const Stackstra\SECONDS_IN_MILLISECOND;
use const Stackstra\SECONDS_IN_MINUTE;
use const Stackstra\SECONDS_IN_MONTH;
use const Stackstra\SECONDS_IN_MONTH_28;
use const Stackstra\SECONDS_IN_MONTH_29;
use const Stackstra\SECONDS_IN_MONTH_30;
use const Stackstra\SECONDS_IN_MONTH_31;
use const Stackstra\SECONDS_IN_NANOSECOND;
use const Stackstra\SECONDS_IN_WEEK;
use const Stackstra\SECONDS_IN_YEAR;
use const Stackstra\SECONDS_IN_YEAR_LEAP;

class Convert
{
	public static function secondsToNanoseconds ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_NANOSECOND,  $precision); }
	public static function secondsToMicroseconds($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_MICROSECOND, $precision); }
	public static function secondsToMilliseconds($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_MILLISECOND, $precision); }
	public static function secondsToMinutes     ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_MINUTE,      $precision); }
	public static function secondsToHours       ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_HOUR,        $precision); }
	public static function secondsToDays        ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_DAY,         $precision); }
	public static function secondsToWeeks       ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_WEEK,        $precision); }
	public static function secondsToMonths      ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_MONTH,       $precision); }
	public static function secondsToMonths28    ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_MONTH_28,    $precision); }
	public static function secondsToMonths29    ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_MONTH_29,    $precision); }
	public static function secondsToMonths30    ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_MONTH_30,    $precision); }
	public static function secondsToMonths31    ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_MONTH_31,    $precision); }
	public static function secondsToYears       ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_YEAR,        $precision); }
	public static function secondsToYearsLeap   ($seconds, $precision = null) { return static::round($seconds / SECONDS_IN_YEAR_LEAP,   $precision); }

	public static function daysToSeconds($days, $precision = null) { return static::round($days * SECONDS_IN_DAY, $precision); }

	public static function bytesToKilobytes($bytes, $precision = null) { return static::round($bytes / BYTES_IN_KILOBYTE, $precision); }
	public static function bytesToMegabytes($bytes, $precision = null) { return static::round($bytes / BYTES_IN_MEGABYTE, $precision); }
	public static function bytesToGigabytes($bytes, $precision = null) { return static::round($bytes / BYTES_IN_GIGABYTE, $precision); }

	public static function kilobytesToBytes($kilobytes, $precision = null) { return static::round($kilobytes * BYTES_IN_KILOBYTE, $precision); }

	public static function megabytesToBytes($megabytes, $precision = null) { return static::round($megabytes * BYTES_IN_MEGABYTE, $precision); }

	public static function microsecondsToSeconds     ($microseconds, $precision = null) { return static::round($microseconds * SECONDS_IN_MICROSECOND,      $precision); }
	public static function microsecondsToMilliseconds($microseconds, $precision = null) { return static::round($microseconds * MILLISECONDS_IN_MICROSECOND, $precision); }

	public static function nanosecondsToSeconds($nanoseconds, $precision = null) { return static::round($nanoseconds * SECONDS_IN_NANOSECOND, $precision); }

	protected static function round($value, $precision = null)
	{
		if ($precision === null)
		{
			return $value;
		}

		return Floats::round($value, $precision);
	}
}

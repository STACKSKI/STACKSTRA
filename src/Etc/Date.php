<?php

namespace Stackstra\Etc;

use DateTime;
use stdClass;

use const Stackstra\SECONDS_IN_WEEK;

class Date
{
	/**
	 * Sets the default timezone
	 *
	 * $success = date::set_timezone('Europe/Minsk'); # bool(true) if success or bool(false) if failed
	 *
	 * @param string $timezone
	 * @return bool
	 */
	public static function timezoneSet(string $timezone): bool
	{
		return date_default_timezone_set($timezone);
	}

	public static function timezoneGet(): string
	{
		return date_default_timezone_get();
	}

	public static function date($separator = '-', $custom_timestamp = null): string|false
	{
		if ($custom_timestamp !== null)
		{
			return date('Y' . $separator . 'm' . $separator . 'd', $custom_timestamp);
		}

		return date('Y' . $separator . 'm' . $separator . 'd');
	}

	public static function time($separator = ':', $custom_timestamp = null): string|false
	{
		if ($custom_timestamp !== null)
		{
			return date('H' . $separator . 'i' . $separator . 's', $custom_timestamp);
		}

		return date('H' . $separator . 'i' . $separator . 's');
	}

	public static function hour(): string|false
	{
		return date('H');
	}

	public static function minute(): string|false
	{
		return date('i');
	}

	public static function timestamp($microseconds = false, $microseconds_get_as_float = false)
	{
		if ($microseconds !== false)
		{
			return microtime($microseconds_get_as_float);
		}

		return time();
	}

	/**
	 * Get current date and time ("YYYY-MM-DD HH:MM:SS")
	 *
	 * $result = date::datetime();         # string(19) "2014-08-31 18:57:49"
	 * $result = date::datetime('/');      # string(19) "2014/08/31 18:57:49"
	 * $result = date::datetime('/', '-'); # string(19) "2014/08/31 18-57-49"
	 *
	 * @param string $separator_date
	 * @param string $separator_time
	 * @param int $custom_timestamp
	 *
	 * @return string|null
	 */
	public static function datetime($separator_date = '-', $separator_time = ':', $custom_timestamp = null)
	{
		if (func_num_args() >= 3 and ($custom_timestamp === null or $custom_timestamp === false or $custom_timestamp < 0))
		{
			return null;
		}

		return Date::date($separator_date, $custom_timestamp) . ' ' . Date::time($separator_time, $custom_timestamp);
	}

	public static function day($timestamp = null)
	{
		if ($timestamp !== null)
		{
			return ((int) date('z', $timestamp)) + 1;
		}

		return ((int) date('z')) + 1;
	}

	public static function week($timestamp = null)
	{
		if ($timestamp !== null)
		{
			return (int) date('W', $timestamp);
		}

		return (int) date('W');
	}

	/**
	 * Get current month ("MM")
	 *
	 * $result = date::month(); # 08
	 *
	 * @param int|null $timestamp
	 *
	 * @return string
	 */
	public static function month($timestamp = null)
	{
		if ($timestamp !== null)
		{
			return date('m', $timestamp);
		}

		return date('m');
	}

	/**
	 * Get current year ("YYYY")
	 *
	 * $result = date::year(); # 2014
	 *
	 * @param int|null $timestamp
	 *
	 * @return string
	 */
	public static function year($timestamp = null)
	{
		if ($timestamp !== null)
		{
			return date('Y', $timestamp);
		}

		return date('Y');
	}

	/**
	 * Total number of days in year
	 *
	 * $result = date::days_in_year();     # int(365) # default year is current (365 days in 2014)
	 * $result = date::days_in_year(2010); # int(365)
	 * $result = date::days_in_year(2004); # int(366)
	 * $result = date::days_in_year(1970); # int(365)
	 *
	 * @param int $year
	 *
	 * @return string
	 */
	public static function daysInYear($year = null)
	{
		if ($year === null)
		{
			$year = Date::year();
		}

		return ((int) date('z', mktime(0, 0, 0, 12, 31, $year))) + 1;
	}

	/**
	 * Total number of days in month
	 *
	 * $result = date::days_in_month();        # int(31) # default year and month are current (31 day in August of 2014)
	 * $result = date::days_in_month(2010);    # int(31) # default month is current (31 day in August of 2010)
	 * $result = date::days_in_month(1970, 4); # int(30) # 30 days in April of 1970
	 * $result = date::days_in_month(1970, 5); # int(31) # 31 day in May of 1970
	 *
	 * @param int $year
	 * @param int $month
	 *
	 * @return int
	 */
	public static function daysInMonth($year = null, $month = null)
	{
		if ($year === null)
		{
			$year = Date::year();
		}

		if ($month === null)
		{
			$month = Date::month();
		}

		return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
	}

	/**
	 * @return int|null null if $string doesn't match $format
	 */
	public static function toTimestamp(string $string, string $format): ?int
	{
		$date = date_create_from_format($format, $string);

		if ($date === false)
		{
			return null;
		}

		return $date->getTimestamp();
	}

	public static function weeksInMonth($year = null, $month = null)
	{
		if ($year === null)
		{
			$year = static::year();
		}

		if ($month === null)
		{
			$month = static::month();
		}


		$month_start = mktime(0, 0, 0, $month, 1, $year);                              # start of month
		$month_end   = mktime(0, 0, 0, $month, date('t', $month_start), $year);        # end of month

		$week_start = (int) date('W', $month_start);                                   # start of week
		$week_end   = (int) date('W', $month_end);                                     # end of week

		if ($week_end < $week_start)
		{
			return ((static::weeksInYear($year - 1) + $week_end) - $week_start) + 1; # month wraps
		}

		return ($week_end - $week_start) + 1;
	}

	public static function weekBorders($year = null, $week = null, $format = 'Y-m-d')
	{
		if ($year === null) { $year = static::year(); }
		if ($week === null) { $week = static::week(); }

		$time = strtotime("1 January $year");

		$day = date('w', $time);

		$time += ((7 * $week) + 1 - $day) * 24 * 3600;

		return [date($format, $time), date($format, $time + SECONDS_IN_WEEK)];
	}

	public static function weeksInYear($year = null)
	{
		if ($year === null)
		{
			$year = static::year();
		}

		$date = new DateTime;
		$date->setISODate($year, 53);

		return ($date->format('W') === '53' ? 53 : 52);
	}

	public static function firstDayOfMonth($year = null, $month = null, $format = 'j')
	{
		if ($year === null)
		{
			$year = static::year();
		}

		if ($month === null)
		{
			$month = static::month();
		}

		return date($format, mktime(0, 0, 0, $month, 1, $year));
	}

	public static function lastDayOfMonth($year = null, $month = null, $format = 'j')
	{
		if ($year === null)
		{
			$year = static::year();
		}

		if ($month === null)
		{
			$month = static::month();
		}

		$month_start = mktime(0, 0, 0, $month, 1, $year);

		return date($format, mktime(0, 0, 0, $month, date('t', $month_start), $year));
	}

	public static function firstDayOfWeek($year = null, $month = null, $week = null, $format = 'j')
	{
		if ($year === null)  { $year  = static::year();  }
		if ($month === null) { $month = static::month(); }
		if ($week === null)  { $week  = static::week();  }

		if ($week == 1)
		{
			return static::firstDayOfMonth($year, $month, $format);
		}

		if ($week > static::weeksInMonth($year, $month))
		{
			return 0;
		}

		$day_of_weak = static::dayOfWeek(static::firstDayOfMonth($year, $month, 'U'));

		$days_in_first_week = 8 - $day_of_weak;

		$day = 7 * ($week - 2) + $days_in_first_week + 1;

		return date($format, strtotime($year . '-' . $month . '-' . $day));
	}

	public static function lastDayOfWeek($year = null, $month = null, $week = null, $format = 'j')
	{
		if ($year === null)  { $year  = static::year();  }
		if ($month === null) { $month = static::month(); }
		if ($week === null)  { $week  = static::week();  }

		if ($week == static::weeksInMonth($year, $month))
		{
			return static::lastDayOfMonth($year, $month, $format);
		}

		if ($week > static::weeksInMonth($year, $month))
		{
			return 0;
		}


		return date($format, strtotime('this sunday', static::firstDayOfWeek($year, $month, $week, 'U')));
	}

	public static function calendar($year)
	{
		$tmp = new stdClass();
		$tmp->id = $year;

		$year = $tmp;
		$year->total_weeks = static::weeksInYear($year->id);
		$year->total_days  = static::daysInYear($year->id);


		$year->months = null;

		for ($i = 1; $i <= 12; $i++)
		{
			$month = new stdClass();
			$month->id = $i;
			$month->total_days  = static::daysInMonth    ($year->id, $month->id);
			$month->total_weeks = static::weeksInMonth   ($year->id, $month->id);
			$month->day_first   = static::firstDayOfMonth($year->id, $month->id);
			$month->day_last    = static::lastDayOfMonth ($year->id, $month->id);

			$month->weeks = null;

			for ($j = 1; $j <= $month->total_weeks; $j++)
			{
				[$week_start, $week_end] = static::weekBorders($year->id, $j);

				$week = new stdClass();
				$week->id = $j;
				$week->is_first  = (int) ($j === 1);
				$week->is_last   = (int) ($j === $month->total_weeks);
				$week->day_first = static::firstDayOfWeek($year->id, $month->id, $week->id);
				$week->day_last  = static::lastDayOfWeek ($year->id, $month->id, $week->id);
				$week->start     = $week_start;
				$week->end       = $week_end;

				$week->total_days = $week->day_last - $week->day_first + 1;

				$month->weeks[$week->id] = $week;
			}

			$year->months[$month->id] = $month;
		}

		return $year;
	}

	public static function dayOfWeek($timestamp = null)
	{
		$day = date('w', $timestamp);

		if ($day === '0')
		{
			return 7;
		}

		return (int) $day;
	}

	public static function isMonday   ($timestamp = null) { return date('w', $timestamp) === '1'; }
	public static function isTuesday  ($timestamp = null) { return date('w', $timestamp) === '2'; }
	public static function isWednesday($timestamp = null) { return date('w', $timestamp) === '3'; }
	public static function isThursday ($timestamp = null) { return date('w', $timestamp) === '4'; }
	public static function isFriday   ($timestamp = null) { return date('w', $timestamp) === '5'; }
	public static function isSaturday ($timestamp = null) { return date('w', $timestamp) === '6'; }
	public static function isSunday   ($timestamp = null) { return date('w', $timestamp) === '0'; }
}

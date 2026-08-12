<?php

namespace Stackstra\Types;

/**
 * Float numbers in programming languages are tricky, so when comparing two floats
 * $a and $b, you should also use an epsilon value. Extra info
 * can be found here: https://stackoverflow.com/a/3149007/1597430
 *
 * @package rude
 */
class Floats
{
	public static float $epsilon = 0.00001;

	public static function rand(int|float $min = 0, int|float $max = 1, int $precision = 2, int $mode = PHP_ROUND_HALF_UP): float
	{
		$rand = $min + mt_rand() / mt_getrandmax() * ($max - $min);

		return static::round($rand, $precision, $mode);
	}

	public static function ceil(int|float $float): float|bool
	{
		return ceil($float);
	}

	public static function floor(int|float $float): float|bool
	{
		return floor($float);
	}

	public static function round(int|float $number, int $precision = 2, int $mode = PHP_ROUND_HALF_UP): float
	{
		return round($number, $precision, $mode);
	}

	protected static function epsilon(?float $epsilon = null): float
	{
		if ($epsilon === null)
		{
			return self::$epsilon;
		}

		return $epsilon;
	}

	public static function isGreater(int|float $a, int|float $b, ?float $epsilon = null): bool
	{
		$epsilon = static::epsilon($epsilon);

		return $a - $epsilon > $b;
	}

	public static function isLess(int|float $a, int|float $b, ?float $epsilon = null): bool
	{
		$epsilon = static::epsilon($epsilon);

		return $a + $epsilon < $b;
	}

	public static function isEqual(int|float $a, int|float $b, ?float $epsilon = null): bool
	{
		$epsilon = static::epsilon($epsilon);

		return abs($a - $b) <= $epsilon;
	}

	public static function isNatural(int|float $a): bool
	{
		return static::isEqual($a, (int) $a) && static::isPositive($a);
	}

	public static function isGreaterOrEqual(int|float $a, int|float $b, ?float $epsilon = null): bool
	{
		return static::isGreater($a, $b, $epsilon) || static::isEqual($a, $b, $epsilon);
	}

	public static function isLessOrEqual(int|float $a, int|float $b, ?float $epsilon = null): bool
	{
		return static::isLess($a, $b, $epsilon) || static::isEqual($a, $b, $epsilon);
	}

	public static function isNotNegative(int|float $a, ?float $epsilon = null): bool
	{
		return static::isGreaterOrEqual($a, 0, $epsilon);
	}

	public static function isNotPositive(int|float $a, ?float $epsilon = null): bool
	{
		return static::isLessOrEqual($a, 0, $epsilon);
	}

	public static function isPositive(int|float $a, ?float $epsilon = null): bool
	{
		return static::isGreater($a, 0, $epsilon);
	}

	public static function isNegative(int|float $a, ?float $epsilon = null): bool
	{
		return static::isLess($a, 0, $epsilon);
	}

	public static function isZero(int|float $a, ?float $epsilon = null): bool
	{
		return static::isEqual($a, 0, $epsilon);
	}

	public static function isBetween(int|float $value, int|float $from, int|float $to, ?float $epsilon = null): bool
	{
		return static::isGreater($value, $from, $epsilon) && static::isLess($value, $to, $epsilon);
	}

	public static function isBetweenOrEqual(int|float $value, int|float $from, int|float $to, ?float $epsilon = null): bool
	{
		return static::isGreaterOrEqual($value, $from, $epsilon) && static::isLessOrEqual($value, $to, $epsilon);
	}

	public static function min(int|float $a, int|float $b, ?float $epsilon = null): float
	{
		$epsilon = static::epsilon($epsilon);

		if (static::isGreater($a, $b, $epsilon))
		{
			return $b;
		}

		return $a;
	}

	public static function max(int|float $a, int|float $b, ?float $epsilon = null): float
	{
		$epsilon = static::epsilon($epsilon);

		if (static::isLess($a, $b, $epsilon))
		{
			return $b;
		}

		return $a;
	}
}
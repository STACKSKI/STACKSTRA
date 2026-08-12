<?php

namespace Stackstra\Types;

class Integer
{
	const INT32_MIN = -2147483648;
	const INT32_MAX =  2147483647;

	const UINT32_MIN = 0;
	const UINT32_MAX = 4294967295;

	/**
	 * Returns random int value
	 *
	 * @param int $min Minimum value of the random number
	 * @param int $max Maximum value of the random number
	 *
	 * @return int A random integer value between min and max
	 */
	public static function rand(int $min, int $max): int
	{
		return mt_rand($min, $max);
	}

	public static function randInt32():  int { return static::rand(self::INT32_MIN,  self::INT32_MAX);  }
	public static function randUint32(): int { return static::rand(self::UINT32_MIN, self::UINT32_MAX); }

	public static function randUnique(int $min, int $max, int $count): array
	{
		$result = [];

		for ($i = 0; $i < $count; $i++)
		{
			$result[] = static::rand($min, $max);
		}

		$result = array_unique($result);

		while (count($result) < $count)
		{
			$number = static::rand($min, $max);

			if (!in_array($number, $result))
			{
				$result[] = $number;
			}
		}

		return $result;
	}

	/**
	 * Check if a number is odd
	 *
	 * @param int $int Any integer
	 *
	 * $is_odd = self::isOdd(1); # bool(true)
	 * $is_odd = self::isOdd(2); # bool(false)
	 * $is_odd = self::isOdd(3); # bool(true)
	 * $is_odd = self::isOdd(4); # bool(false)
	 * $is_odd = self::isOdd(5); # bool(true)
	 * $is_odd = self::isOdd(6); # bool(false)
	 *
	 * @return bool
	 */
	public static function isOdd(int $int): bool
	{
		return (bool) ($int & 1);
	}

	/**
	 * Checks if a number is even
	 *
	 * @param int $int Any integer
	 *
	 * $is_even = self::isEven(1); # bool(false)
	 * $is_even = self::isEven(2); # bool(true)
	 * $is_even = self::isEven(3); # bool(false)
	 * $is_even = self::isEven(4); # bool(true)
	 * $is_even = self::isEven(5); # bool(false)
	 * $is_even = self::isEven(6); # bool(true)
	 *
	 * @return bool
	 */
	public static function isEven(int $int): bool
	{
		return (bool) (~$int & 1);
	}

	public static function isPositive(int $int): bool
	{
		return $int > 0;
	}

	public static function isNegative(int $int): bool
	{
		return $int < 0;
	}

	/**
	 * Decimal to binary converter
	 *
	 * @param int $int Any integer
	 *
	 * @return string Binary representation
	 */
	public static function toBin(int $int): string
	{
		return decbin($int);
	}

	/**
	 * Decimal to hexadecimal converter
	 *
	 * @param int $int Any integer
	 *
	 * @return string Hexadecimal representation
	 */
	public static function toHex(int $int): string
	{
		return dechex($int);
	}

	public static function inRange(int $number, int $from, int $to, bool $include_borders = true): bool
	{
		if ($include_borders)
		{
			return $from <= $number && $number <= $to;
		}

		return $from < $number && $number < $to;
	}
}
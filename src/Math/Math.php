<?php

namespace Stackstra\Math;

/**
 * @category math
 */
class Math
{
	/**
	 * Factorial calculator
	 *
	 * $result = math::factorial(3); # int(6)
	 *
	 * @param int $number
	 *
	 * @return int
	 */
	public static function factorial($number)
	{
		return $number ? $number * Math::factorial($number - 1) : 1;
	}

	public static function fibonacci($number)
	{
		return round(pow((sqrt(5) + 1) / 2, $number - 1) / sqrt(5));
	}

	/**
	 * Determine if a number is prime
	 *
	 * @param int $number
	 *
	 * @return bool
	 */
	public static function isPrime($number)
	{
		# 1 is not prime, see: http://en.wikipedia.org/wiki/Prime_number#Primality_of_one
		if ($number == 1)
		{
			return false;
		}

		# 2 is prime (the only even number that is prime)
		if ($number == 2)
		{
			return true;
		}

		/**
		 * if the number is divisible by two, then it's not prime and it's no longer
		 * needed to check other even numbers
		 */
		if ($number % 2 == 0)
		{
			return false;
		}

		/**
		 * Checks the odd numbers. If any of them is a factor, it returns false.
		 * The sqrt can be an approximation, so to be safe it is rounded
		 * up to the next highest integer value.
		 */
		for ($i = 3; $i <= ceil(sqrt($number)); $i = $i + 2)
		{
			if ($number % $i == 0)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Generating prime numbers (sieve of Eratosthenes algorithm)
	 *
	 * $result = math::primes(100); # Array
	 *                              # (
	 *                              #     [0] => 2
	 *                              #     [1] => 3
	 *                              #     [2] => 5
	 *                              #     [3] => 7
	 *                              #     [4] => 11
	 *                              #     [5] => 13
	 *                              #     [6] => 17
	 *                              #     [7] => 19
	 *                              #     [8] => 23
	 *                              #     [9] => 29
	 *                              #     [10] => 31
	 *                              #     [11] => 37
	 *                              #     [12] => 41
	 *                              #     [13] => 43
	 *                              #     [14] => 47
	 *                              #     [15] => 53
	 *                              #     [16] => 59
	 *                              #     [17] => 61
	 *                              #     [18] => 67
	 *                              #     [19] => 71
	 *                              #     [20] => 73
	 *                              #     [21] => 79
	 *                              #     [22] => 83
	 *                              #     [23] => 89
	 *                              #     [24] => 97
	 *                              # )
	 *
	 * @param int $limit Sets the upper bound for searching prime numbers
	 * @return array
	 */
	public static function primes($limit = 1000)
	{
		$numbers = array_fill(0, $limit, true);

		$numbers[0] = false;
		$numbers[1] = false;

		for ($i = 2; $i < $limit; $i++)
		{
			if ($numbers[$i])
			{
				for ($j = 2; $i * $j < $limit; $j++)
				{
					$numbers[$i * $j] = false;
				}
			}
		}


		$result = null;

		foreach ($numbers as $number => $is_prime)
		{
			if ($is_prime)
			{
				$result[] = $number;
			}
		}

		return $result;
	}

	/**
	 * @param array    $numbers array of numbers
	 * @param int|null $n       number of periods (window size) for the moving average
	 *
	 * @return array
	 */
	public static function exponentialMovingAverage($numbers, $n = null)
	{
		if ($n === null)
		{
			$n = count($numbers);
		}


		$alpha = 2 / ($n + 1);

		$numbers = array_values($numbers);

		$ema = [array_shift($numbers)];

		foreach ($numbers as $index => $number)
		{
			$ema[] = ($alpha * $number) + ((1 - $alpha) * end($ema));
		}

		return $ema;
	}
}
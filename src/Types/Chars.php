<?php

namespace Stackstra\Types;

class Chars
{
	/**
	 * @param string $string
	 * @param int    $n      1-indexed character position
	 *
	 * @return string|null null if $n is out of range
	 */
	public static function nth(string $string, int $n): ?string
	{
		if ($n < 1 or $n > static::count($string))
		{
			return null;
		}

		return Strings::read($string, 1, $n - 1);
	}

	public static function count(string $string): int
	{
		return Strings::length($string);
	}

	/**
	 * Picks $n random characters from $string (with repetition)
	 */
	public static function rand(string $string, int $n = 1): string
	{
		$result = '';


		$length = Strings::length($string);

		for ($i = 0; $i < $n; $i++)
		{
			$result .= static::nth($string, Integer::rand(1, $length));
		}

		return $result;
	}

	/**
	 * Get the first character of a string
	 *
	 * $char = char::first('Hello'); # string(1) "H"
	 *
	 * @param string $string Any string
	 * @param int    $length
	 *
	 * @return string
	 */
	public static function first(string $string, int $length = 1): string
	{
		return mb_substr($string, 0, $length);
	}

	public static function second(string $string): ?string { return static::nth($string, 2); }
	public static function third (string $string): ?string { return static::nth($string, 3); }
	public static function fourth(string $string): ?string { return static::nth($string, 4); }

	/**
	 * Get the last character of a string
	 *
	 * $char = char::last('Hello'); # string(1) "o"
	 *
	 * @param string $string Any string
	 * @param int    $length
	 *
	 * @return string
	 */
	public static function last(string $string, int $length = 1): string
	{
		return mb_substr($string, -$length, $length);
	}

	/**
	 * Swaps the characters at positions $a and $b (see Items::swap() for the position rules)
	 */
	public static function swap(string $string, string $a, string $b): string
	{
		$chars = Strings::chars($string);

		$chars = Items::swap($chars, $a, $b);

		return implode('', $chars);
	}

	/**
	 * Remove first character(s) from string
	 *
	 * @param string   $string Any string
	 * @param int      $count
	 * @param int|null $length
	 *
	 * @return string
	 */
	public static function removeFirst(string $string, int $count = 1, ?int $length = null): string
	{
		     if ($length === null) { return mb_substr($string, $count);          }
		else                       { return mb_substr($string, $count, $length); }
	}

	/**
	 * Remove last character(s) from string
	 *
	 * $string = char::remove_last('Hello'); # string(4) "Hell"
	 *
	 * @param string $string Any string
	 * @param int    $count
	 *
	 * @return string
	 */
	public static function removeLast(string $string, int $count = 1): string
	{
		return mb_substr($string, 0, -$count);
	}

	/**
	 * Removes (in place) and returns the first $count character(s) of $string
	 */
	public static function popFirst(string &$string, int $count = 1): string
	{
		$char = static::first($string, $count);

		$string = static::removeFirst($string, $count);

		return $char;
	}

	/**
	 * Removes (in place) and returns the last $count character(s) of $string
	 */
	public static function popLast(string &$string, int $count = 1): string
	{
		$char = static::last($string, $count);

		$string = static::removeLast($string, $count);

		return $char;
	}

	/**
	 * Count unique chars in the string
	 *
	 * @param string $string Any string
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $count = char::count_unique($string_ASCII); # int(17)
	 * $count = char::count_unique($string_UTF8);  # int(21)
	 *
	 * @return int
	 */
	public static function countUnique(string $string): int
	{
		return count(static::unique($string));
	}

	public static function unique(string $string): array
	{
		$chars = Strings::chars($string);

		$uniqie = array_unique($chars);

		return array_values($uniqie);
	}

	/**
	 * Get characters frequency
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = char::frequency($string_ASCII); # Array
	 *                                           # (
	 *                                           #     [A] => 1
	 *                                           #     [S] => 1
	 *                                           #     [C] => 1
	 *                                           #     [I] => 2
	 *                                           #     [ ] => 2
	 *                                           #     [s] => 1
	 *                                           #     [t] => 1
	 *                                           #     [r] => 1
	 *                                           #     [i] => 1
	 *                                           #     [n] => 1
	 *                                           #     [g] => 1
	 *                                           #     [e] => 2
	 *                                           #     [x] => 1
	 *                                           #     [a] => 1
	 *                                           #     [m] => 1
	 *                                           #     [p] => 1
	 *                                           #     [l] => 1
	 *                                           # )
	 *
	 * $result = char::frequency($string_UTF8);  # Array
	 *                                           # (
	 *                                           #     [U] => 1
	 *                                           #     [T] => 1
	 *                                           #     [F] => 1
	 *                                           #     [-] => 1
	 *                                           #     [8] => 1
	 *                                           #     [ ] => 2
	 *                                           #     [s] => 1
	 *                                           #     [t] => 1
	 *                                           #     [r] => 1
	 *                                           #     [i] => 1
	 *                                           #     [n] => 1
	 *                                           #     [g] => 1
	 *                                           #     [π] => 1
	 *                                           #     [ρ] => 1
	 *                                           #     [ά] => 1
	 *                                           #     [δ] => 1
	 *                                           #     [ε] => 1
	 *                                           #     [ι] => 1
	 *                                           #     [γ] => 1
	 *                                           #     [μ] => 1
	 *                                           #     [α] => 1
	 *                                           # )
	 *
	 * @param string $string Any string
	 *
	 * @return array
	 */
	public static function frequency(string $string): array
	{
		$unique = [];

		foreach (Strings::chars($string) as $char)
		{
			     if (!isset($unique[$char])) { $unique[$char] = 1; }
			else                             { $unique[$char]++;   }
		}

		return $unique;
	}

	/**
	 * Check for zero-terminate character (or first character in the provided string)
	 *
	 * $is_null = char::is_null("0");  # bool(false)
	 * $is_null = char::is_null("A");  # bool(false)
	 * $is_null = char::is_null("\n"); # bool(false)
	 * $is_null = char::is_null("\0"); # bool(true)
	 *
	 * @param string $char Any string
	 *
	 * @return bool
	 */
	public static function isNull(string $char): bool
	{
		return static::first($char) === "\0";
	}

	/**
	 * Check if character (or first character in the provided string) is printable: letters, digit or blank
	 *
	 * $is_printable = char::is_printable("A");  # bool(true)
	 * $is_printable = char::is_printable("B");  # bool(true)
	 * $is_printable = char::is_printable(" ");  # bool(true)
	 * $is_printable = char::is_printable("\0"); # bool(false)
	 *
	 * @param string $char Any string
	 * @return bool
	 */
	public static function isPrintable(string $char): bool
	{
		return ctype_print(static::first($char));
	}

	/**
	 * Detect charset of character
	 *
	 * @param $char
	 * @return bool
	 */
	public static function isUTF8(string $char): bool
	{
		return Strings::is_utf8(static::first($char));
	}

	public static function toHex(string $char): string
	{
		return bin2hex($char);
	}
}
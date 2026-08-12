<?php

namespace Stackstra\Types;

use Stackstra\Exceptions\Exceptions;
use Stackstra\Lang\English;
use Stackstra\Stream\Stream;

use function Stackstra\false_to_null;

if (!defined('APP_STRING_NEWLINE_WIN')) define('APP_STRING_NEWLINE_WIN', chr(0xD) . chr(0xA)); # 0xD 0xA <=> CL RF <=> \r\n (Windows)
if (!defined('APP_STRING_NEWLINE_LIN')) define('APP_STRING_NEWLINE_LIN',            chr(0xA)); #     0xA <=>    RF <=>   \n (Linux)
if (!defined('APP_STRING_NEWLINE_MAC')) define('APP_STRING_NEWLINE_MAC', chr(0xD)           ); # 0xD     <=> CL    <=> \r   (Macintosh)

class Strings
{
	/**
	 * @param int    $length
	 * @param string $alphabet
	 *
	 * @return string
	 */
	public static function rand(int $length = 32, string $alphabet = English::ALPHABET): string
	{
		$alphabet_length = self::length($alphabet);

		try
		{
			$stream = random_bytes($length * 2);
		}
		catch (\Throwable $e)
		{
			Exceptions::warning($e->getMessage());

			$stream = self::randMersenne($length, $alphabet);
		}

		$bytes = Stream::decodeUint16($stream);

		$result = '';

		foreach ($bytes as $byte)
		{
			$result .= $alphabet[$byte % $alphabet_length];
		}

		return $result;
	}

	/**
	 * Fallback for rand() when random_bytes() fails to gather enough entropy; uses mt_rand() instead
	 */
	public static function randMersenne(int $length = 32, string $alphabet = English::ALPHABET): string
	{
		$result = '';

		$alphabet_size = static::length($alphabet);

		for ($i = 0; $i < $length; $i++)
		{
			$number = mt_rand(1, $alphabet_size);

			$result .= static::char($alphabet, $number);
		}

		return $result;
	}

	/**
	 * Find position of occurrence of a string
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::find($string_ASCII, 'example');  # int(13)
	 * $result = strings::find($string_UTF8, 'πράδειγμα'); # int(13)
	 *
	 * @param string    $string Any string
	 * @param string    $substring Any substring
	 * @param int|null  $offset
	 * @param bool      $case_sensitive
	 *
	 * @return int|false
	 */
	public static function find(string $string, string $substring, ?int $offset = null, bool $case_sensitive = true): int|false
	{
		if ($case_sensitive)
		{
			return mb_strpos($string, $substring, $offset ?? 0);
		}

		return mb_stripos($string, $substring, $offset ?? 0);
	}

	/**
	 * @param string   $string
	 * @param string   $substring
	 * @param int|null $offset
	 *
	 * @return int|false
	 */
	public static function findCI(string $string, string $substring, ?int $offset = null): int|false
	{
		return self::find($string, $substring, $offset, false);
	}

	/**
	 * Find position of first occurrence of a string
	 *
	 * @param string   $string
	 * @param string   $substring
	 * @param int|null $offset
	 * @param bool     $case_sensitive
	 *
	 * @return int|false
	 */
	public static function findLast(string $string, string $substring, ?int $offset = null, bool $case_sensitive = true): int|false
	{
		if ($case_sensitive)
		{
			return mb_strrpos($string, $substring, $offset ?? 0);
		}

		return mb_strripos($string, $substring, $offset ?? 0);
	}

	/**
	 * @param string   $string
	 * @param string   $substring
	 * @param int|null $offset
	 *
	 * @return int|false
	 */
	public static function findLastCI(string $string, string $substring, ?int $offset = null): int|false
	{
		return self::findLast($string, $substring, $offset, false);
	}

	/**
	 * Find position of nth occurrence of a string
	 *
	 * @param string   $string
	 * @param string   $substring
	 * @param int      $nth
	 * @param int|null $offset
	 * @param bool     $case_sensitive
	 *
	 * @return int|false
	 */
	public static function findNth(string $string, string $substring, int $nth, ?int $offset = null, bool $case_sensitive = true): int|false
	{
		$position = static::find($string, $substring, $offset, $case_sensitive);

		if ($position === false)
		{
			return false;
		}

		$substring_length = static::length($substring);

		for ($i = 1; $i < $nth; $i++)
		{
			$position = static::find($string, $substring, $position + $substring_length, $case_sensitive);

			if ($position === false)
			{
				return false;
			}
		}

		return $position;
	}

	/**
	 * @param string   $string
	 * @param string   $substring
	 * @param int      $nth
	 * @param int|null $offset
	 *
	 * @return int|false
	 */
	public static function findNthCI(string $string, string $substring, int $nth, ?int $offset = null): int|false
	{
		return self::findNth($string, $substring, $nth, $offset, false);
	}

	/**
	 * Find position of first occurrence of a string
	 *
	 * @param string   $string
	 * @param string   $substring
	 * @param int|null $offset
	 * @param bool     $case_sensitive
	 *
	 * @return int|false
	 */
	public static function findFirst(string $string, string $substring, ?int $offset = null, bool $case_sensitive = true): int|false
	{
		return static::findNth($string, $substring, 1, $offset, $case_sensitive);
	}

	/**
	 * @param string   $string
	 * @param string   $substring
	 * @param int|null $offset
	 *
	 * @return int|false
	 */
	public static function findFirstCI(string $string, string $substring, ?int $offset = null): int|false
	{
		return self::findFirst($string, $substring, $offset, false);
	}

	/**
	 * Find position of second occurrence of a string
	 *
	 * @param string    $string
	 * @param string    $substring
	 * @param int|null  $offset
	 * @param bool|true $case_sensitive
	 *
	 * @return int|false
	 */
	public static function findSecond(string $string, string $substring, ?int $offset = null, bool $case_sensitive = true): int|false
	{
		return static::findNth($string, $substring, 2, $offset, $case_sensitive);
	}

	/**
	 * @param string   $string
	 * @param string   $substring
	 * @param int|null $offset
	 *
	 * @return int|false
	 */
	public static function findSecondCI(string $string, string $substring, ?int $offset = null): int|false
	{
		return self::findSecond($string, $substring, $offset, false);
	}

	/**
	 * Find position of third occurrence of a string
	 *
	 * @param string   $string
	 * @param string   $substring
	 * @param int|null $offset
	 * @param bool     $case_sensitive
	 *
	 * @return int|false
	 */
	public static function findThird(string $string, string $substring, ?int $offset = null, bool $case_sensitive = true): int|false
	{
		return static::findNth($string, $substring, 3, $offset, $case_sensitive);
	}

	/**
	 * @param string   $string
	 * @param string   $substring
	 * @param int|null $offset
	 *
	 * @return int|false
	 */
	public static function findThirdCI(string $string, string $substring, ?int $offset = null): int|false
	{
		return self::findThird($string, $substring, $offset, false);
	}

	/**
	 * Return the string's size (bytes)
	 *
	 * @param string $string
	 *
	 * @return int
	 */
	public static function size(string $string): int
	{
		# strlen cannot be trusted anymore because of mbstring.func_overload

		return (int) mb_strlen($string, '8bit');
	}

	/**
	 * Return the string's length (chars)
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::length($string_ASCII); # int(20)
	 * $result = strings::length($string_UTF8);  # int(22)
	 *
	 * @param string $string
	 *
	 * @return int The length of the string in characters
	 */
	public static function length(string $string): int
	{
		return mb_strlen($string);
	}

	/**
	 * Count the number of occurrences of a substring in a string
	 *
	 * $string_ASCII = 'ASCII string example example';     # string(28) "ASCII string example example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα πράδειγμα'; # string(50) "UTF-8 string πράδειγμα πράδειγμα"
	 *
	 * $result = strings::count($string_ASCII, 'example');  # int(2)
	 * $result = strings::count($string_UTF8, 'πράδειγμα'); # int(2)
	 *
	 * @param string $string Any string
	 * @param string $substring Any substring
	 *
	 * @return int The number of substrings in the string
	 */
	public static function count(string $string, string $substring): int
	{
		return mb_substr_count($string, $substring);
	}

	/**
	 * Count words in the string
	 *
	 * @param string      $string   Any string
	 * @param string|null $charlist A list of additional characters to be considered part of a word
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $count = strings::count_words($string_ASCII); # int(3) # Array
	 *                                                       # (
	 *                                                       #     [0] => ASCII
	 *                                                       #     [1] => string
	 *                                                       #     [2] => example
	 *                                                       # )
	 *
	 *
	 * # if you want to use this method with UTF-8 strings - you must specify language alphabet for the correct results:
	 *
	 * $alphabet = '1234567890-ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩαάβγδεζηθικλμνξοπρστυφχψω';
	 *
	 *
	 * $count = strings::count_words($string_UTF8, $alphabet); # int(3) # Array
	 *                                                                 # (
	 *                                                                 #     [0] => UTF-8
	 *                                                                 #     [1] => string
	 *                                                                 #     [2] => πράδειγμα
	 *                                                                 # )
	 *
	 * @return int The number of words in the text
	 */
	public static function countWords(string $string, ?string $charlist = null): int
	{
		return str_word_count(str_replace("\xC2\xAD", '', $string), 0, $charlist); # "\xC2\xAD" is a "SOFT HYPHEN" character
	}

	/**
	 * Count lines in the string
	 *
	 * @param string $string Any string
	 * @param string $delimiter Line delimiter
	 *
	 * $count = strings::count_lines("Text\nwith\nnewlines!"); # int(3)
	 *
	 * @return int The number of lines in the text
	 */
	public static function countLines(string $string, string $delimiter = PHP_EOL): int
	{
		return static::count($string, $delimiter) + 1;
	}

	/**
	 * Get excerpt from string
	 *
	 * @param string $string
	 * @param int    $max_length
	 * @param string $ending
	 *
	 * @return string
	 */
	public static function excerpt(string $string, int $max_length, string $ending = '...'): string
	{
		$max_length = $max_length - static::length($ending);

		if (static::length($string) <= $max_length)
		{
			return $string;
		}

		return static::read($string, $max_length) . $ending;
	}

	/**
	 * Replace occurrences of the search string with the replacement string
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::replace($string_ASCII, 'example', 'πράδειγμα'); # string(31) "ASCII string πράδειγμα"
	 * $result = strings::replace($string_UTF8, 'πράδειγμα', 'example');  # string(20) "UTF-8 string example"
	 *
	 * @param string       $string Any string
	 * @param string|array $substring Any string or array of strings
	 * @param string|array $replace Any string or array of strings
	 * @param bool         $case_sensitive Case-sensitive/insensitive flag
	 *
	 * @return string
	 */
	public static function replace(string $string, array|string $substring, array|string $replace, $case_sensitive = true): string
	{
		if (is_array($substring))
		{
			foreach ($substring as $item)
			{
				$string = static::replace($string, $item, $replace, $case_sensitive);
			}

			return $string;
		}


		if ($case_sensitive)
		{
			return str_replace($substring, $replace, $string);
		}

		return str_ireplace($substring, $replace, $string);
	}

	/**
	 * Replace first occurrence of string
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::replace_first($string_ASCII, ' ', 'aaa'); # string(22) "ASCIIaaastring example"
	 * $result = strings::replace_first($string_UTF8, ' ', 'άάά');  # string(36) "UTF-8άάάstring πράδειγμα"
	 *
	 * @param string   $string Any string
	 * @param string   $substring Any substring
	 * @param string   $replace Any string
	 * @param bool     $case_sensitive
	 * @param int|null $offset
	 *
	 * @return string Replaced string
	 */
	public static function replaceFirst(string $string, string $substring, string $replace, bool $case_sensitive = true, ?int $offset = null): string
	{
		if ($substring === '')
		{
			return $string;
		}


		$pos = static::find($string, $substring, $offset, $case_sensitive);

		if ($pos !== false)
		{
			$string = substr_replace($string, $replace, $pos, static::size($substring));
		}

		return $string;
	}

	/**
	 * Replace last occurrence of string
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::replace_last($string_ASCII, ' ', 'aaa'); # string(22) "ASCII stringaaaexample"
	 * $result = strings::replace_last($string_UTF8, ' ', 'άάά');  # string(36) "UTF-8 stringάάάπράδειγμα"
	 *
	 * @param string   $string Any string
	 * @param string   $substring Any substring
	 * @param string   $replace Any string
	 * @param bool     $case_sensitive
	 * @param int|null $offset
	 *
	 * @return string
	 */
	public static function replaceLast(string $string, string $substring, string $replace, $case_sensitive = true, $offset = null): string
	{
		if ($substring === '')
		{
			return $string;
		}


		$pos = static::findLast($string, $substring, $offset, $case_sensitive);

		if ($pos !== false)
		{
			$string = substr_replace($string, $replace, $pos, static::size($substring));
		}

		return $string;
	}

	/**
	 * Replace occurrence of string between two markers
	 *
	 * @param string   $string
	 * @param string   $substring_one
	 * @param string   $substring_two
	 * @param string   $replace
	 * @param bool     $include_first_border
	 * @param bool     $include_second_border
	 * @param int|null $offset
	 *
	 * @return string
	 */
	public static function replaceBetween(string $string, string $substring_one, string $substring_two, string $replace, bool $include_first_border = false, bool $include_second_border = false, ?int $offset = null): string
	{
		$substring = static::readBetween($string, $substring_one, $substring_two, $include_first_border, $include_second_border, $offset);

		return static::replace($string, $substring, $replace);
	}

	/**
	 * Remove last occurrence of string
	 *
	 * @param string $string
	 * @param string $substring
	 * @param bool   $case_sensitive
	 *
	 * @return string
	 */
	public static function remove(string $string, string $substring, bool $case_sensitive = true): string
	{
		return static::replace($string, $substring, '', $case_sensitive);
	}

	/**
	 * Remove first occurrence of string
	 *
	 * @param string $string
	 * @param string $substring
	 * @param bool   $case_sensitive
	 *
	 * @return string
	 */
	public static function removeFirst(string $string, string $substring, bool $case_sensitive = true): string
	{
		return static::replaceFirst($string, $substring, '', $case_sensitive);
	}

	/**
	 * Remove first occurrence of string
	 *
	 * @param string $string
	 * @param string $substring
	 * @param bool   $case_sensitive
	 *
	 * @return string
	 */
	public static function removeLast(string $string, string $substring, bool $case_sensitive = true): string
	{
		return static::replaceLast($string, $substring, '', $case_sensitive);
	}

	/**
	 * Remove empty lines in a string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function removeEmptyLines(string $string): string
	{
		$lines = static::lines($string);

		foreach ($lines as $index => $line)
		{
			if (!static::trim($line))
			{
				unset ($lines[$index]);
			}
		}

		return Items::implode($lines, PHP_EOL);
	}

	/**
	 * @param string   $string
	 * @param string   $substring_one
	 * @param string   $substring_two
	 * @param bool     $include_first_border
	 * @param bool     $include_second_border
	 * @param int|null $offset
	 * @param bool     $case_sensitive
	 *
	 * @return string
	 */
	public static function removeBetween(string $string, string $substring_one, string $substring_two, bool $include_first_border = false, bool $include_second_border = false, ?int $offset = null, bool $case_sensitive = true): string
	{
		$length_one = static::length($substring_one);
		$length_two = static::length($substring_two);

		for (;;)
		{
			$length = static::length($string);

			if ($length === 0 or $offset > $length)
			{
				break;
			}


			$offset_one = static::find($string, $substring_one, $offset,         $case_sensitive);
			$offset_two = static::find($string, $substring_two, $offset_one + 1, $case_sensitive);

			if ($offset_one === false or $offset_two === false)
			{
				break;
			}


			if (!$include_first_border)  { $offset_one += $length_one; }
			if ( $include_second_border) { $offset_two += $length_two; }

			$string = static::read($string, $offset_one) . static::read($string, null, $offset_two);

			$offset = $offset_one + 1;
		}

		return $string;
	}

	/**
	 * Return part of a string with a specific length in characters
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::read($string_ASCII, 5, 13); # string(5) "examp"
	 * $result = strings::read($string_UTF8, 5, 13);  # string(10) "πράδε"
	 *
	 * @param string $string Any string
	 * @param null   $length Substring length to read
	 * @param int    $offset String offset
	 *
	 * @return string
	 */
	public static function read(string $string, $length = null, $offset = 0): string
	{
		return mb_substr($string, $offset, $length);
	}

	/**
	 * Get the substring after a specific substring
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::read_after($string_ASCII, 'string '); # string(7) "example"
	 * $result = strings::read_after($string_UTF8, 'string ');  # string(18) "πράδειγμα"
	 *
	 * @param string $string Any string
	 * @param string $substring Any substring
	 *
	 * @return string|null
	 */
	public static function readAfter(string $string, string $substring): ?string
	{
		if ($substring === '')
		{
			return null;
		}

		$pos = static::find($string, $substring);

		if ($pos !== false)
		{
			return static::read($string, static::length($string) - $pos, $pos + static::length($substring));
		}

		return null;
	}

	/**
	 * Get the substring which starts with a specific substring
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::read_from($string_ASCII, 'string'); # string(14) "string example"
	 * $result = strings::read_from($string_UTF8, 'string');  # string(25) "string πράδειγμα"
	 *
	 * @param string $string Any string
	 * @param string $substring Any substring
	 *
	 * @return string|null
	 */
	public static function readFrom(string $string, string $substring): ?string
	{
		$pos = static::find($string, $substring);

		if ($pos !== false)
		{
			return static::read($string, static::length($string) - $pos, $pos);
		}

		return null;
	}

	/**
	 * Get the substring which ends with a specific substring
	 *
	 * @param string $string
	 * @param string $substring
	 * @param bool   $exclude_substring
	 *
	 * @return string|null
	 */
	public static function readUntil(string $string, string $substring, bool $exclude_substring = true): ?string
	{
		$pos = static::find($string, $substring);

		if ($pos !== false)
		{
			if ($exclude_substring)
			{
				return static::read($string, $pos);
			}

			return static::read($string, $pos + static::length($substring));
		}

		return null;
	}

	/**
	 * Get the substring which is located between two specific substrings
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::read_between($string_ASCII, 'ex', 'le'); # string(3) "amp"
	 * $result = strings::read_between($string_UTF8, 'πρ', 'μα');  # string(10) "άδειγ"
	 *
	 * $result = strings::read_between($string_ASCII, 'ex', 'le', true); # string(7) "example"
	 * $result = strings::read_between($string_UTF8, 'πρ', 'μα', true);  # string(18) "πράδειγμα"
	 *
	 * @param string   $string
	 * @param string   $substring_one
	 * @param string   $substring_two
	 * @param bool     $include_first_border
	 * @param bool     $include_second_border
	 * @param int|null $offset
	 *
	 * @return string|null
	 */
	public static function readBetween(string $string, string $substring_one, string $substring_two, bool $include_first_border = false, bool $include_second_border = false, ?int $offset = null): ?string
	{
		$pos_one = static::find($string, $substring_one, $offset);

		if ($pos_one === false)
		{
			return null;
		}

		$pos_two = static::find($string, $substring_two, $pos_one + 1);

		if ($pos_two === false)
		{
			return null;
		}

		$size_one = static::length($substring_one);

		$substring = static::read($string, $pos_two - $pos_one - $size_one, $pos_one + $size_one);

		if ($include_first_border  !== false) { $substring = $substring_one . $substring; }
		if ($include_second_border !== false) { $substring = $substring . $substring_two; }

		return $substring;
	}

	/**
	 * Reverse the string
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function reverse(string $string): string
	{
		$chars = static::chars($string);

		$chars = array_reverse($chars);

		return implode($chars);
	}

	/**
	 * Strip whitespace characters from the beginning and end of a string
	 *
	 * @param string       $string
	 * @param array|string $trim
	 *
	 * @return string
	 */
	public static function trim(string $string, array|string $trim = [" ", "\t", "\n", "\r", "\0", "\x0B"]): string
	{
		$string = static::trimLeft($string, $trim);
		$string = static::trimRight($string, $trim);

		return $string;
	}

	/**
	 * Strip whitespace characters from the beginning of a string
	 *
	 * @param string       $string
	 * @param array|string $trim
	 *
	 * @return string
	 */
	public static function trimLeft(string $string, array|string $trim = [" ", "\t", "\n", "\r", "\0", "\x0B"]): string
	{
		if (is_string($trim))
		{
			$trim = static::chars($trim);
		}

		$trim = array_flip($trim);


		for ($string_length = static::length($string), $trim_length = 0, $i = 1; $i <= $string_length; $i++)
		{
			$char = Chars::nth($string, $i);

			     if (isset($trim[$char])) { $trim_length++; }
			else                          { break;          }
		}

		if ($trim_length)
		{
			return Chars::removeFirst($string, $trim_length);
		}

		return $string;
	}

	/**
	 * Strip whitespace characters from the end of a string
	 *
	 * @param string       $string
	 * @param array|string $trim
	 *
	 * @return string
	 */
	public static function trimRight(string $string, array|string $trim = [" ", "\t", "\n", "\r", "\0", "\x0B"]): string
	{
		if (is_string($trim))
		{
			$trim = static::chars($trim);
		}

		$trim = array_flip($trim);


		for ($string_length = static::length($string), $trim_length = 0, $i = $string_length; $i > 0; $i--)
		{
			$char = Chars::nth($string, $i);

			     if (isset($trim[$char])) { $trim_length++; }
			else                          { break;          }
		}

		if ($trim_length)
		{
			return Chars::removeLast($string, $trim_length);
		}

		return $string;
	}


	/**
	 * Split a string by substring
	 *
	 * $result = strings::split("First line\nSecond line\nThird line"); # Array
	 *                                                                  # (
	 *                                                                  #     [0] => First line
	 *                                                                  #     [1] => Second line
	 *                                                                  #     [2] => Third line
	 *                                                                  # )
	 *
	 * $result = strings::split('String Array Object', ' '); # Array
	 *                                                       # (
	 *                                                       #     [0] => String
	 *                                                       #     [1] => Array
	 *                                                       #     [2] => Object
	 *                                                       # )
	 *
	 * $result = strings::split('baby,son,mom,dad', ','); # Array
	 *                                                    # (
	 *                                                    #     [0] => baby
	 *                                                    #     [1] => son
	 *                                                    #     [2] => mom
	 *                                                    #     [3] => dad
	 *                                                    # )
	 *
	 * @param string   $string    Any string
	 * @param string   $delimiter String delimiter (newline by default)
	 * @param int|null $limit     Maximum number of elements to return
	 *
	 * @return array
	 */
	public static function explode(string $string, string $delimiter = PHP_EOL, ?int $limit = null): array
	{
		if ($limit === null)
		{
			return explode($delimiter, $string);
		}

		return explode($delimiter, $string, $limit);
	}

	/**
	 * Join array elements with a string
	 *
	 * @param        $string
	 * @param string $glue
	 *
	 * @return string
	 */
	public static function implode($string, $glue = ', '): string
	{
		return implode($glue, $string);
	}

	/**
	 * Check if a string contains the specified substring
	 *
	 * $string = 'Shit happens';
	 * $substring = 'hit';
	 *
	 * $is_contains = strings::contains($string, $substring); # bool(true)
	 *
	 * @param string          $string         Any string
	 * @param string|string[] $substring      Any substring or array of substrings
	 * @param bool            $case_sensitive `true` for case sensitive search and `false` otherwise
	 * @param int|null        $offset         offset
	 *
	 * @return bool
	 */
	public static function contains(string $string, string|array $substring, bool $case_sensitive = true, ?int $offset = null): bool
	{
		if (is_array($substring))
		{
			foreach ($substring as $item)
			{
				if (static::contains($string, $item, $case_sensitive, $offset))
				{
					return true;
				}
			}

			return false;
		}

		return static::find($string, $substring, $offset, $case_sensitive) !== false;
	}

	/**
	 * @return bool true if $string contains any of $substrings
	 */
	public static function containsAny(string $string, array $substrings): bool
	{
		foreach ($substrings as $substring)
		{
			if (static::contains($string, $substring))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool true if $string contains every one of $substrings
	 */
	public static function containsAll(string $string, array $substrings): bool
	{
		foreach ($substrings as $substring)
		{
			if (!static::contains($string, $substring))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @return bool true if every char in $string is one of $chars
	 */
	public static function containsOnly(string $string, array $chars): bool
	{
		$chars = array_flip($chars);

		foreach (static::chars($string) as $char)
		{
			if (!isset($chars[$char]))
			{
				return false;
			}
		}

		return true;
	}

	public static function containsLetters(string $string): bool
	{
		return (bool) preg_match('/[A-Za-z]/', $string);
	}

	public static function containsDigits(string $string): bool
	{
		return (bool) preg_match('/[0-9]/', $string);
	}

	/**
	 * Check if a string starts with the specified character/string or not
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::starts_with($string_ASCII, 'example');  # bool(false)
	 * $result = strings::starts_with($string_ASCII, 'ASCII');    # bool(true)
	 *
	 * $result = strings::starts_with($string_UTF8, 'πράδειγμα'); # bool(false)
	 * $result = strings::starts_with($string_UTF8, RUDE_STRING_ENCODING);     # bool(true)
	 *
	 * @param string          $string Any string
	 * @param string|string[] $substring Any substring or array of substrings
	 * @param bool            $case_sensitive
	 *
	 * @return bool
	 */
	public static function startsWith(string $string, array|string $substring, bool $case_sensitive = true): bool
	{
		if (is_array($substring))
		{
			foreach ($substring as $sub)
			{
				if (static::startsWith($string, $sub, $case_sensitive))
				{
					return true;
				}
			}

			return false;
		}

		if ($case_sensitive !== true)
		{
			$string    = static::toLowercase($string);
			$substring = static::toLowercase($substring);
		}

		//if (!is_string(   $string)) {    $string = (string)    $string; }
		//if (!is_string($substring)) { $substring = (string) $substring; }

		return str_starts_with($string, $substring);
	}

	/**
	 * Check if a string ends with the specified character/string or not
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::ends_with($string_ASCII, 'example');  # bool(true)
	 * $result = strings::ends_with($string_ASCII, 'ASCII');    # bool(false)
	 *
	 * $result = strings::ends_with($string_UTF8, 'πράδειγμα'); # bool(true)
	 *
	 * @param string       $string    Any string
	 * @param string|array $substring Any substring or array of substrings
	 *
	 * @param bool $case_sensitive
	 *
	 * @return bool
	 */
	public static function endsWith(string $string, string|array $substring, bool $case_sensitive = true): bool
	{
		if (is_array($substring))
		{
			foreach ($substring as $sub)
			{
				if (static::endsWith($string, $sub, $case_sensitive))
				{
					return true;
				}
			}

			return false;
		}


		$length = strlen($substring);

		if (!$length)
		{
			return false;
		}


		if ($case_sensitive !== true)
		{
			$string    = static::toLowercase($string);
			$substring = static::toLowercase($substring);
		}

		return str_ends_with($string, $substring);
	}

	/**
	 * Erase chars in the string after the specific position
	 *
	 * @param string   $string Any string
	 * @param int      $offset
	 * @param int|null $length
	 *
	 * @return string
	 */
	public static function erase(string $string, int $offset, ?int $length = null): string
	{
		if ($length === null)
		{
			# string: `hello`
			# offset: 2
			# length: null
			# result: `he`

			return substr($string, 0, $offset);
		}

		if ($offset == 0)
		{
			# string: `hello`
			# offset: 0
			# length: 2
			# result: `llo`

			return static::read($string, static::length($string) - $length, $length);
		}


		# string: `hello`
		# offset: 2
		# length: 2
		# result: `heo`

		return static::read($string, $offset) . substr($string, $offset + $length);
	}

	/**
	 * Insert string at specified position
	 *
	 * $string = strings::insert('AAAA BBBB CCCC', 'DDDD', 5); # string(18) "AAAA DDDDBBBB CCCC"
	 *
	 * @param string $string Any string
	 * @param string $substring Any substring
	 * @param int    $offset Substring offset for insert
	 *
	 * @return string
	 */
	public static function insert(string $string, string $substring, int $offset): string
	{
		if ($offset == 0)
		{
			return $string . $substring;
		}

		return substr($string, 0, $offset) . $substring . substr($string, $offset);
	}

	/**
	 * Generate all permutations of a given string
	 *
	 *
	 * $result = strings::permutation('AAA BBB CCC DDD'); # Array
	 *                                                   # (
	 *                                                   #     [0] => AAA BBB CCC DDD
	 *                                                   #     [1] => AAA BBB DDD CCC
	 *                                                   #     [2] => AAA CCC DDD BBB
	 *                                                   #     [3] => AAA CCC BBB DDD
	 *                                                   #     [4] => AAA DDD BBB CCC
	 *                                                   #     [5] => AAA DDD CCC BBB
	 *                                                   #     [6] => BBB CCC DDD AAA
	 *                                                   #     [7] => BBB CCC AAA DDD
	 *                                                   #     [8] => BBB DDD AAA CCC
	 *                                                   #     [9] => BBB DDD CCC AAA
	 *                                                   #     [10] => BBB AAA CCC DDD
	 *                                                   #     [11] => BBB AAA DDD CCC
	 *                                                   #     [12] => CCC DDD AAA BBB
	 *                                                   #     [13] => CCC DDD BBB AAA
	 *                                                   #     [14] => CCC AAA BBB DDD
	 *                                                   #     [15] => CCC AAA DDD BBB
	 *                                                   #     [16] => CCC BBB DDD AAA
	 *                                                   #     [17] => CCC BBB AAA DDD
	 *                                                   #     [18] => DDD AAA BBB CCC
	 *                                                   #     [19] => DDD AAA CCC BBB
	 *                                                   #     [20] => DDD BBB CCC AAA
	 *                                                   #     [21] => DDD BBB AAA CCC
	 *                                                   #     [22] => DDD CCC AAA BBB
	 *                                                   #     [23] => DDD CCC BBB AAA
	 *                                                   # )
	 *
	 *
	 * @param string $string Any string
	 * @param string $delimiter
	 *
	 * @return string[]
	 */
	public static function permutation(string $string, string $delimiter = ' '): array
	{
		$array = Items::permutation(explode($delimiter, $string));


		$result = null;

		foreach ($array as $item)
		{
			$result[] = implode(' ', $item);
		}

		return $result;
	}

	/**
	 * Returns the first line from the string
	 *
	 * $string = "Hello\nHi\nWow!\n12345";
	 *
	 * $line = strings::first($string); # string(5) "Hello"
	 *
	 * @param string $string Any string
	 * @param string $delimiter String delimiter
	 *
	 * @return string|null
	 */
	public static function first(string $string, string $delimiter = PHP_EOL): ?string
	{
		return static::line($string, 1, $delimiter);
	}

	/**
	 * Returns the last line from the string
	 *
	 * $string = "Hello\nHi\nWow!\n12345";
	 *
	 * $line = strings::last($string); # string(5) "12345"
	 *
	 * @param string $string Any string
	 * @param string $delimiter String delimiter
	 *
	 * @return string|null
	 */
	public static function last(string $string, string $delimiter = PHP_EOL): ?string
	{
		$count = static::countLines($string);

		return static::line($string, $count, $delimiter);
	}

	/**
	 * Return specific character from the string
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $char = strings::char($string_ASCII, 14); # string(1) "e"
	 * $char = strings::char($string_ASCII, 15); # string(1) "x"
	 * $char = strings::char($string_ASCII, 16); # string(1) "a"
	 *
	 * $char = strings::char($string_UTF8, 14); # string(2) "π"
	 * $char = strings::char($string_UTF8, 15); # string(2) "ρ"
	 * $char = strings::char($string_UTF8, 16); # string(2) "ά"
	 *
	 * @param string $string Any string
	 * @param int    $number Character number in the range from 1 to n (string length)
	 *
	 * @return string
	 */
	public static function char(string $string, int $number): string
	{
		return static::read($string, 1, $number - 1);
	}

	/**
	 * Convert a string to an array of chars
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $chars = strings::chars($string_ASCII); # Array
	 *                                         # (
	 *                                         #     [0] => A
	 *                                         #     [1] => S
	 *                                         #     [2] => C
	 *                                         #     [3] => I
	 *                                         #     [4] => I
	 *                                         #     [5] =>
	 *                                         #     [6] => s
	 *                                         #     [7] => t
	 *                                         #     [8] => r
	 *                                         #     [9] => i
	 *                                         #     [10] => n
	 *                                         #     [11] => g
	 *                                         #     [12] =>
	 *                                         #     [13] => e
	 *                                         #     [14] => x
	 *                                         #     [15] => a
	 *                                         #     [16] => m
	 *                                         #     [17] => p
	 *                                         #     [18] => l
	 *                                         #     [19] => e
	 *                                         # )
	 *
	 * $chars = strings::chars($string_UTF8);  # Array
	 *                                         # (
	 *                                         #     [0] => U
	 *                                         #     [1] => T
	 *                                         #     [2] => F
	 *                                         #     [3] => -
	 *                                         #     [4] => 8
	 *                                         #     [5] =>
	 *                                         #     [6] => s
	 *                                         #     [7] => t
	 *                                         #     [8] => r
	 *                                         #     [9] => i
	 *                                         #     [10] => n
	 *                                         #     [11] => g
	 *                                         #     [12] =>
	 *                                         #     [13] => π
	 *                                         #     [14] => ρ
	 *                                         #     [15] => ά
	 *                                         #     [16] => δ
	 *                                         #     [17] => ε
	 *                                         #     [18] => ι
	 *                                         #     [19] => γ
	 *                                         #     [20] => μ
	 *                                         #     [21] => α
	 *                                         # )
	 *
	 * @param string $string Any string
	 *
	 * @return string[]
	 */
	public static function chars(string $string): array
	{
		# "u (PCRE8) This modifier turns on additional functionality of PCRE that is incompatible with Perl.
		# Pattern strings are treated as UTF-8. This modifier is available from PHP 4.1.0 or greater on Unix
		# and from PHP 4.2.3 on win32. UTF-8 validity of the pattern is checked since PHP 4.3.5."

		return preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Returns byte representation of string
	 *
	 * @param $string
	 *
	 * @return array
	 */
	public static function bytes(string $string): array
	{
		return unpack('C*', $string);
	}

	/**
	 * Convert a string to an array
	 *
	 * @param string $string
	 * @param int    $split_length
	 *
	 * @return string[]
	 */
	public static function split(string $string, int $split_length = 0): array
	{
		if ($split_length == 0)
		{
			return preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
		}


		$result = [];

		$length = static::length($string);

		for ($i = 0; $i < $length; $i += $split_length)
		{
			$result[] = static::read($string, $split_length, $i);
		}

		return $result;
	}

	/**
	 * Returns the specified line from the string
	 *
	 * $string = "Hello\nHi\nWow!\n12345";
	 *
	 * $line = strings::line($string, 3); # string(4) "Wow!"
	 *
	 * @param string $string Any string
	 * @param int    $number Line number (in the range of 1 to n)
	 * @param string $delimiter Line delimiter
	 *
	 * @return string|null
	 */
	public static function line(string $string, int $number, string $delimiter = PHP_EOL): ?string
	{
		$lines = explode($delimiter, $string);

		if (count($lines) < $number)
		{
			return null;
		}

		return $lines[$number - 1];
	}

	/**
	 * Return specific lines from the string (or all lines if range is not provided)
	 *
	 * $string = "First line\nSecond line\nThird line\netc";
	 *
	 * $lines = strings::lines($string); # Array
	 *                                   # (
	 *                                   #     [0] => First line
	 *                                   #     [1] => Second line
	 *                                   #     [2] => Third line
	 *                                   #     [3] => etc
	 *                                   # )
	 *
	 * $lines = strings::lines($string, 2); # Array
	 *                                      # (
	 *                                      #     [0] => Second line
	 *                                      #     [1] => Third line
	 *                                      #     [2] => etc
	 *                                      # )
	 *
	 * $lines = strings::lines($string, 2, 3); # Array
	 *                                         # (
	 *                                         #     [0] => Second line
	 *                                         #     [1] => Third line
	 *                                         # )
	 *
	 *
	 * @param string   $string Any string
	 * @param int|null $from
	 * @param int|null $to
	 * @param string   $delimiter Line delimiter
	 *
	 * @return array
	 */
	public static function lines(string $string, ?int $from = null, ?int $to = null, string $delimiter = PHP_EOL): array
	{
		if ($from === null)
		{
			return explode($delimiter, $string);
		}

		if ($to === null)
		{
			$to = static::count($string, $delimiter) + 1;
		}

		if ($from < 1 or $from > $to)
		{
			return [];
		}


		$lines = explode($delimiter, $string);

		if (count($lines) < $to)
		{
			return [];
		}


		$result = [];

		for ($i = $from - 1; $i <= $to - 1; $i++)
		{
			$result[] = $lines[$i];
		}

		return $result;
	}

	/**
	 * Appends (and/or prepends) $substring to every line in the $from..$to range
	 */
	public static function linesAppend(string $string, string $substring, ?string $from = null, ?string $to = null, string $delimiter = PHP_EOL, bool $append_after = true, bool $append_before = false, bool $skip_last = false): string
	{
		$lines = static::lines($string, $from, $to, $delimiter);

		$lines = Items::append($lines, $substring, $append_after, $append_before, $skip_last);

		return static::implode($lines, $delimiter);
	}

	public static function lines_append_before(string $string, string $substring, ?string $from = null, ?string $to = null, string $delimiter = PHP_EOL, bool $skip_last = false): string { return static::linesAppend($string, $substring, $from, $to, $delimiter, false,  true, $skip_last); }
	public static function lines_append_after (string $string, string $substring, ?string $from = null, ?string $to = null, string $delimiter = PHP_EOL, bool $skip_last = false): string { return static::linesAppend($string, $substring, $from, $to, $delimiter,  true, false, $skip_last); }
	public static function lines_append_both  (string $string, string $substring, ?string $from = null, ?string $to = null, string $delimiter = PHP_EOL, bool $skip_last = false): string { return static::linesAppend($string, $substring, $from, $to, $delimiter,  true,  true, $skip_last); }


	/**
	 * Return specified word from the string
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::word($string_ASCII, 1); # string(5) "ASCII"
	 * $result = strings::word($string_ASCII, 2); # string(6) "string"
	 * $result = strings::word($string_ASCII, 3); # string(7) "example"
	 *
	 *
	 * # if you want to use this method with UTF-8 strings - you must specify language alphabet for the correct results:
	 *
	 * $alphabet = '1234567890-ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩαάβγδεζηθικλμνξοπρστυφχψω';
	 *
	 *
	 * $result = strings::word($string_UTF8, 1, $alphabet); # string(5) "UTF-8"
	 * $result = strings::word($string_UTF8, 2, $alphabet); # string(6) "string"
	 * $result = strings::word($string_UTF8, 3, $alphabet); # string(18) "πράδειγμα"
	 *
	 * @param string      $string
	 * @param int         $number
	 * @param string|null $charlist
	 *
	 * @return string
	 */
	public static function word(string $string, int $number, ?string $charlist = null): string
	{
		# TODO: rename to word_nth or nth_word?

		return str_word_count($string, 1, $charlist)[$number - 1];
	}

	/**
	 * Return all words from the string
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 *
	 * $result = strings::words($string_ASCII);  debug($result, true); # Array
	 *                                                                 # (
	 *                                                                 #     [0] => ASCII
	 *                                                                 #     [1] => string
	 *                                                                 #     [2] => example
	 *                                                                 # )
	 *
	 *
	 * # if you want to use this method with UTF-8 strings - you must specify language alphabet for the correct results:
	 *
	 * $alphabet = '1234567890-ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩαάβγδεζηθικλμνξοπρστυφχψω';
	 *
	 *
	 * $result = strings::words($string_UTF8, $alphabet); # Array
	 *                                                    # (
	 *                                                    #     [0] => UTF-8
	 *                                                    #     [1] => string
	 *                                                    #     [2] => πράδειγμα
	 *                                                    # )
	 *
	 * @param string      $string
	 * @param string|null $charlist
	 *
	 * @return array
	 */
	public static function words(string $string, ?string $charlist = null): array
	{
		return str_word_count($string, 1, $charlist);
	}

	/**
	 * Strips everything except digits and a leading sign, then drops a leading '+'
	 */
	public static function digits(string $string): string
	{
		$string = filter_var($string, FILTER_SANITIZE_NUMBER_INT);

		if (static::startsWith($string, '+'))
		{
			$string = Chars::removeFirst($string);
		}

		return $string;
	}

	/**
	 * Strips everything except digits, keeping a leading '+' or '-' sign
	 */
	public static function number(string $string): string
	{
		$string = static::trim($string);

		$char_first = Chars::first($string);

		$sign = match ($char_first)
		{
			'-', '+' => $char_first,

			default => '',
		};

		return $sign . preg_replace('/\D/', '', $string);
	}

	//public static function numbers($string)
	//{
	//	# https://stackoverflow.com/a/6278312
	//
	//	preg_match_all('!\d+!', $string, $matches);
	//
	//	return get($matches, 0, []);
	//}

	/**
	 * Strips every char not present in $allowed_chars
	 */
	public static function escape(string $string, ?string $allowed_chars = English::ALPHABET): string
	{
		$allowed_chars = static::chars($allowed_chars);
		$allowed_chars = array_flip($allowed_chars);

		$chars = static::chars($string);

		foreach ($chars as $index => $char)
		{
			if (!isset($allowed_chars[$char]))
			{
				unset($chars[$index]);
			}
		}

		return static::implode($chars, '');
	}

	/**
	 * @return bool true if $string_a and $string_b are the same string
	 */
	public static function equal(string $string_a, string $string_b, bool $case_sensitive = true): bool
	{
		if (!$case_sensitive)
		{
			$string_a = static::toLowercase($string_a);
			$string_b = static::toLowercase($string_b);
		}

		return !strcmp($string_a, $string_b);
	}

	public static function equalCI(string $string_a, string $string_b): bool
	{
		return static::equal($string_a, $string_b, false);
	}

	/**
	 * Detect charset of string
	 *
	 * $is_utf8 = strings::is_utf8('ABCDEFАБВГДЕ'); # bool(true)
	 *
	 * @param $string
	 * @return bool
	 */
	public static function is_utf8(string $string): bool
	{
		########################################################################################
		# 1. Any UTF8 string is a valid 8-bit encoding string (even if it produces gibberish); #
		# 2. On the other hand, most 8-bit encoded strings with extended (128+) characters are #
		#    not valid UTF8, but, as any other random byte sequence, they might happen to be;  #
		# 3. Of course, any ASCII text is valid UTF8;                                          #
		# 4. Native mb_detect_encoding() is slow.                                              #
		########################################################################################

		return preg_match('//u', $string);
	}

	/**
	 * Check if a string contains BOM signature
	 *
	 * @param string $string Any string
	 *
	 * @return bool
	 */
	public static function isBom(string $string): bool
	{
		return (substr($string, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf));
	}

	/**
	 * Check if string is serialized
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function isSerialized(string $string): bool
	{
		return @unserialize($string) !== false;
	}

	/**
	 * Check if a string is empty
	 *
	 * @param string $string
	 *
	 * @return bool
	 *
	 */
	public static function isEmpty(string $string): bool
	{
		return empty($string);
	}

	/**
	 * Check if a string is an email address
	 *
	 * @param string $string Any string
	 *
	 * @return bool
	 */
	public static function isEmail(string $string): bool
	{
		return filter_var($string, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Check if a string is an IP address
	 *
	 * @param string $string Any string
	 *
	 * @return bool
	 */
	public static function isIP(string $string): bool
	{
		return filter_var($string, FILTER_VALIDATE_IP);
	}

	public static function isUppercase(string $string): bool { return $string === mb_strtoupper($string); }
	public static function isLowercase(string $string): bool { return $string === mb_strtolower($string); }

	/**
	 * Check if a string is an URL address
	 *
	 * @param string $string Any string
	 *
	 * @return bool
	 */
	public static function isURL(string $string): bool
	{
		return filter_var($string, FILTER_VALIDATE_URL);
	}

	public static function is_number(string $string): bool
	{
		return is_numeric($string);
	}

	public static function isInteger(string $string): bool
	{
		return ctype_digit($string);
	}

	public static function isJSON(string $string): bool
	{
		return @json_decode($string) !== null;
	}

	public static function parseFloat(string $string): ?float
	{
		$result = filter_var($string, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

		return false_to_null($result);
	}

	public static function parseInt(string $string): ?int
	{
		$result = filter_var($string, FILTER_SANITIZE_NUMBER_INT);

		return false_to_null($result);
	}

	//public static function equals($string_one, $string_two, $case_sensitive = true)
	//{
	//	if (!is_string($string_one)) { $string_one = (string) $string_one; }
	//	if (!is_string($string_two)) { $string_two = (string) $string_two; }
	//
	//	if (!$case_sensitive)
	//	{
	//		$string_one = static::to_lowercase($string_one);
	//		$string_two = static::to_lowercase($string_two);
	//	}
	//
	//	return $string_one === $string_two;
	//}

	/**
	 * Unpack data from binary string
	 *
	 * @param string $format
	 * @param string $data
	 *
	 * @return mixed
	 */
	public static function unpack(string $format, string $data): mixed
	{
		$array = unpack($format, $data); # use origin unpack function

		return reset($array); # get the first item from the array
	}

	/**
	 * Pad a string with another string
	 *
	 * @param string $string
	 * @param int    $pad_length
	 * @param string $pad_string
	 * @param int    $pad_direction
	 *
	 * @return string
	 */
	public static function pad(string $string, int $pad_length, string $pad_string = ' ', int $pad_direction = STR_PAD_RIGHT): string
	{
		return str_pad($string, $pad_length, $pad_string, $pad_direction);
	}

	/**
	 * Pad a string with another string to the left
	 *
	 * @param string $string
	 * @param int    $pad_length
	 * @param string $pad_string
	 *
	 * @return string
	 */
	public static function padLeft(string $string, int $pad_length, string $pad_string = ' '): string
	{
		return static::pad($string, $pad_length, $pad_string, STR_PAD_LEFT);
	}

	/**
	 * Pad a string with another string to the right
	 *
	 * @param string $string
	 * @param int    $pad_length
	 * @param string $pad_string
	 *
	 * @return string
	 */
	public static function padRight(string $string, int $pad_length, string $pad_string = ' '): string
	{
		return static::pad($string, $pad_length, $pad_string, STR_PAD_RIGHT);
	}

	/**
	 * Pad a string with another string in both directions
	 *
	 * @param string $string
	 * @param int    $pad_length
	 * @param string $pad_string
	 *
	 * @return string
	 */
	public static function padBoth(string $string, int $pad_length, string $pad_string = ' '): string
	{
		return static::pad($string, $pad_length, $pad_string, STR_PAD_BOTH);
	}

	/**
	 * Repeat a string
	 *
	 * @param string $string
	 * @param int    $multiplier
	 *
	 * @return string
	 */
	public static function repeat(string $string, int $multiplier): string
	{
		return str_repeat($string, $multiplier);
	}

	/**
	 * Convert character encoding to UTF-8
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function toUTF8(string $string): string
	{
		return mb_convert_encoding($string, 'UTF-8', 'auto');
	}

	/**
	 * Make a string uppercase
	 *
	 * $string_ASCII = 'ASCII string example';   # string(20) "ASCII string example"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα'; # string(31) "UTF-8 string πράδειγμα"
	 *
	 * $result = strings::to_uppercase($string_ASCII); # string(20) "ASCII STRING EXAMPLE"
	 * $result = strings::to_uppercase($string_UTF8);  # string(31) "UTF-8 STRING ΠΡΆΔΕΙΓΜΑ"
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function toUppercase(string $string): string
	{
		return mb_strtoupper($string);
	}

	/**
	 * Make a string lowercase
	 *
	 * $string_ASCII = 'ASCII STRING EXAMPLE';   # string(20) "ASCII STRING EXAMPLE"
	 * $string_UTF8  = 'UTF-8 STRING ΠΡΆΔΕΙΓΜΑ'; # string(31) "UTF-8 STRING ΠΡΆΔΕΙΓΜΑ"
	 *
	 * $result = strings::to_lowercase($string_ASCII); # string(20) "ascii string example"
	 * $result = strings::to_lowercase($string_UTF8);  # string(31) "utf-8 string πράδειγμα"
	 *
	 * @param string $string Any string
	 *
	 * @return string
	 */
	public static function toLowercase(string $string): string
	{
		return mb_strtolower($string);
	}

	/**
	 * Capitalize the first character of a string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function toCapitalcase(string $string): string
	{
		$char_first = Chars::first($string);
		$char_rest  = static::read($string, static::length($string) - 1, 1);

		return static::toUppercase($char_first) . static::toLowercase($char_rest);
	}

	/**
	 * Convert a string to title case
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function toTitlecase(string $string): string
	{
		return mb_convert_case($string, MB_CASE_TITLE);
	}

	/**
	 * Converts spaces/punctuation to underscores and inserts an underscore before each new
	 * camelCase word boundary, then lowercases the result
	 */
	public static function toSnakecase(string $string): string
	{
		$string = str_replace([' ', '__', '__'], '_', $string);
		$string = preg_replace('/(?!_)\p{P}/u', '', $string);

		$string_lowercase = static::toLowercase($string);

		$chars_lowercase = static::chars($string_lowercase);


		$result = '';

		$chars = static::chars($string);

		foreach ($chars as $index => $char)
		{
			if ($char === '_')
			{
				$result .= $char;

				continue;
			}

			if ($char === $chars_lowercase[$index])
			{
				$result .= $char;


				if (isset($chars[$index + 1]))
				{
					$char_next = $chars[$index + 1];

					if ($char_next !== '_' and $chars_lowercase[$index + 1] !== $char_next)
					{
						$result .= '_';
					}
				}
			}
			else
			{
				$result .= $chars_lowercase[$index];
			}
		}

		return trim($result, '_');
	}

	/***
	 * Remove duplicates from the string
	 *
	 * $string_ASCII = 'ASCII ASCII string example string';       # string(33) "ASCII ASCII string example string"
	 * $string_UTF8  = 'UTF-8 string πράδειγμα string πράδειγμα'; # string(57) "UTF-8 string πράδειγμα string πράδειγμα"
	 *
	 * $result = strings::remove_duplicates($string_ASCII); # string(20) "ASCII string example"
	 * $result = strings::remove_duplicates($string_UTF8);  # string(31) "UTF-8 string πράδειγμα"
	 *
	 * @param string $string Any string
	 * @param string $delimiter String delimiter
	 *
	 * @return string
	 */
	public static function removeDuplicates(string $string, string $delimiter = ' '): string
	{
		return implode($delimiter, array_unique(explode($delimiter, $string)));
	}

	/**
	 * Remove numbers from the string
	 *
	 * @param string $string Any string
	 *
	 * @return string
	 */
	public static function removeNumbers(string $string): string
	{
		return preg_replace('/[0-9]+/', '', $string);
	}

	/**
	 * Remove BOM from the string
	 *
	 * @param string $string Any string
	 *
	 * @return string
	 */
	public static function removeBOM(string $string): string
	{
		if (substr($string, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf))
		{
			return substr($string, 3);
		}

		return $string;
	}
}

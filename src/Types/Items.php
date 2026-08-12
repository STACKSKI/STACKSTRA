<?php

namespace Stackstra\Types;

use ArrayAccess;
use Traversable;
use SimpleXMLElement;

use function Stackstra\get;

class Items
{
	/**
	 * @param array|object $array
	 * @param mixed        $keys  array of nested keys to walk (dot-path style); a single key falls back to the global get()
	 * @param mixed        $default
	 *
	 * @return mixed
	 */
	public static function get(array|object $array, mixed $keys, mixed $default = null): mixed
	{
		if (!is_array($keys))
		{
			return get($array, $keys, $default);
		}


		$pointer = &$array;

		foreach ($keys as $key)
		{
			# property_exists does not work properly with magic properties and a few other cases, so isset should be used as an extra validation

			$is_array_access = $pointer instanceof ArrayAccess;

			if ((is_array($pointer) or $is_array_access) and (isset($pointer[$key]) or array_key_exists($key, $pointer)))
			{
				     if ($is_array_access) { $pointer =  $pointer[$key]; }
				else                       { $pointer = &$pointer[$key]; }
			}
			else if (is_object($pointer) and (isset($pointer->{$key}) or property_exists($pointer, $key)))
			{
				$pointer = $pointer->{$key};
			}
			else
			{
				return $default;
			}
		}

		return $pointer;
	}

	public static function getByKeys($array, $keys)
	{
		return array_intersect_key($array, array_flip($keys));
	}

	/**
	 * @param array $array
	 * @param int   $n     1-indexed position
	 *
	 * @return mixed null if $n is out of range
	 */
	public static function nth(array $array, int $n): mixed
	{
		$i = 1;

		foreach ($array as $item)
		{
			if ($i == $n)
			{
				return $item;
			}

			$i++;
		}

		return null;
	}

	/**
	 * @param array $array
	 * @param int   $n     1-indexed position
	 *
	 * @return int|string|null null if $n is out of range
	 */
	public static function nthKey(array $array, int $n): int|string|null
	{
		$i = 1;

		foreach ($array as $index => $item)
		{
			if ($i == $n)
			{
				return $index;
			}

			$i++;
		}

		return null;
	}

	/**
	 * @param array $array
	 * @param int   $n     1-indexed position counting from the end
	 *
	 * @return mixed null if $n is out of range
	 */
	public static function nthToLast(array $array, int $n): mixed
	{
		if ($n < 1 or $n > count($array))
		{
			return null;
		}

		$values = array_values($array);

		return $values[count($values) - $n];
	}

	/**
	 * Get the first element of an array
	 *
	 * @template T
	 *
	 * @param array<T>   $array
	 * @param ?int  $length
	 * @param int   $offset
	 * @param bool  $preserve_keys
	 * @param bool  $force_array
	 *
	 * @return T|T[]
	 */
	public static function first(array $array, ?int $length = 1, int $offset = 0, bool $preserve_keys = true, bool $force_array = false): mixed
	{
		if (!$array)
		{
			return null;
		}

		if ($length === 1 and $offset === 0)
		{
			$result = reset($array);

			if ($result === false and count($array) === 0)
			{
				$result = null;
			}

			     if ($force_array) { return [$result]; }
			else                   { return  $result;  }
		}

		return array_slice($array, $offset, $length, $preserve_keys);
	}

	public static function second(array $array) { return static::nth($array, 2); }
	public static function third (array $array) { return static::nth($array, 3); }

	/**
	 * Get the last element of an array
	 *
	 * @param array $array
	 * @param int   $length
	 * @param int   $offset
	 * @param bool  $preserve_keys
	 * @param bool  $force_array
	 *
	 * @return mixed
	 */
	public static function last(array $array, int $length = 1, int $offset = 0, bool $preserve_keys = true, bool $force_array = false): mixed
	{
		if (!$array)
		{
			return null;
		}

		if ($length === 1 and $offset === 0)
		{
			$result = end($array);

			if ($result === false and count($array) === 0)
			{
				$result = null;
			}

			     if ($force_array) { return [$result]; }
			else                   { return  $result;  }
		}

		return array_slice($array, -$length -$offset, $length, $preserve_keys);
	}

	public static function secondToLast(array $array) { return static::nthToLast($array, 2); }
	public static function thirdToLast (array $array) { return static::nthToLast($array, 3); }

	/**
	 * @param array $array
	 * @param int   $index_first re-key starting from this offset instead of 0
	 *
	 * @return array
	 */
	public static function reindex(array $array, int $index_first = 0): array
	{
		$values = array_values($array);

		if ($index_first === 0)
		{
			return $values;
		}

		return array_slice($values, $index_first);
	}

	//	public static function first_index($array) { return key(array_slice($array,  0, 1, true)); }
//	public static function last_index($array)  { return key(array_slice($array, -1, 1, true)); }

	public static function keyExist(array $array, string|int $key): bool
	{
		return isset($array[$key]) || array_key_exists($key, $array);
	}

	public static function keyFirst(array $array): int|string|null { return array_key_first($array); }
	public static function keyLast (array $array): int|string|null { return array_key_last ($array); }

	/**
	 * @param array $array
	 * @param array $keys    keys to pull, in order
	 * @param mixed $default used for any key missing from $array
	 *
	 * @return array
	 */
	public static function only(array $array, array $keys, mixed $default = null): array
	{
		$result = [];

		foreach ($keys as $key)
		{
			$result[$key] = array_key_exists($key, $array) ? $array[$key] : $default;
		}

		return $result;
	}

	/**
	 * Shift an element off the beginning of an array
	 *
	 * @param      $array
	 * @param int  $n
	 * @param bool $return_array_only
	 * @param bool $preserve_keys
	 *
	 * @return array|mixed
	 */
	public static function shift(&$array, $n = 1, $preserve_keys = true, $return_array_only = false)
	{
		$result = [];

		reset($array);

		if ($preserve_keys)
		{
			$array_keys = array_keys($array);

			for ($i = 0; $i < $n and $array; $i++)
			{
				$result[array_shift($array_keys)] = array_shift($array);
			}
		}
		else
		{
			for ($i = 0; $i < $n and $array; $i++)
			{
				$result[] = array_shift($array);
			}
		}

		if ($n === 1 and !$return_array_only)
		{
			return Items::first($result);
		}

		return $result;
	}

	/**
	 * @param array $array
	 * @param int   $n
	 * @param bool  $return_array_only forces an array return even when $n === 1
	 *
	 * @return mixed
	 */
	public static function pop(array &$array, int $n = 1, bool $return_array_only = false): mixed
	{
		if ($n === 1)
		{
			     if ($return_array_only === false) { return  array_pop($array) ; }
			else                                   { return [array_pop($array)]; }
		}


		$result = [];

		for ($i = 0; $i < $n and $array; $i++)
		{
			$result[] = array_pop($array);
		}

		return $result;
	}

	public static function push(array &$array, $item): int
	{
		return array_push($array, $item);
	}

	public static function unshift(array &$array, $item): int
	{
		return array_unshift($array, $item);
	}

	//public static function pull(&$haystack, $needle, $default = null)
	//{
	//	return pull($haystack, $needle, $default);
	//}

	/**
	 * @param mixed $needle single value, or array of values checked with containsAny()
	 *
	 * @return bool
	 */
	public static function contains($haystack, $needle): bool
	{
		if (!$haystack)
		{
			return false;
		}

		if (is_array($needle))
		{
			return self::containsAny($haystack, $needle);
		}

		return in_array($needle, $haystack);
	}

	/**
	 * @return bool true if $haystack contains any of $needles
	 */
	public static function containsAny(array $haystack, array $needles): bool
	{
		if (!$haystack or !$needles)
		{
			return false;
		}

		return (bool) array_intersect($haystack, $needles);
	}

	/**
	 * @return bool true if $haystack contains every one of $needles
	 */
	public static function containsAll(array $haystack, array $needles): bool
	{
		if (!$haystack or !$needles)
		{
			return false;
		}

		return !array_diff($needles, $haystack);
	}

	public static function rand($items, $n = 1)
	{
		return static::randLibc($items, $n);
	}

	/**
	 * Pick one or more random entries out of an array
	 *
	 * @param array $array
	 * @param int   $n
	 *
	 * @return mixed
	 */
	public static function randLibc(array $array, int $n = 1): array
	{
		$array_keys = array_rand($array, $n);

		return array_intersect_key($array, array_flip($array_keys));
	}

	/**
	 * @param array      $array
	 * @param int|string $a     numeric positions are treated as 1-indexed, non-numeric values are used as literal keys
	 * @param int|string $b     see $a
	 *
	 * @return array
	 */
	public static function swap(array &$array, int|string $a, int|string $b): array
	{
		if (is_numeric($a)) { $a = $a - 1; }
		if (is_numeric($b)) { $b = $b - 1; }

		$temp      = $array[$a];
		$array[$a] = $array[$b];
		$array[$b] = $temp;

		return $array;
	}

	/**
	 * Same as trim(), just for all elements of the array
	 *
	 * @param array        $array
	 * @param array|string $chars
	 *
	 * @return array
	 */
	public static function trim(array $array, array|string $chars = [" ", "\t", "\n", "\r", "\0", "\x0B"]): array
	{
		foreach ($array as $index => $item)
		{
			$array[$index] = Strings::trim($item, $chars);
		}

		return $array;
	}

	/**
	 * Same as trim(), just applied to the array's keys instead of its values
	 *
	 * @param array        $array
	 * @param array|string $chars
	 *
	 * @return array
	 */
	public static function trimKeys(array $array, array|string $chars = [" ", "\t", "\n", "\r", "\0", "\x0B"]): array
	{
		$result = [];

		foreach ($array as $index => $item)
		{
			$result[Strings::trim($index, $chars)] = $item;
		}

		return $result;
	}

	/**
	 * Count all occurrences of specified values inside an array
	 *
	 * $haystack = array('a', 'b', 'f', 'r', 'b', 'v', 'r', 'b', 't', 'a');
	 * $needle = array('a', 'b');
	 *
	 * $count = array_count($needle, $haystack); # int(2)
	 *
	 * @param array $needle
	 * @param array $haystack
	 *
	 * @return int
	 */
	public static function count(array $needle, array $haystack): int
	{
		$count = INF;

		$array = array_count_values($haystack);

		foreach ($needle as $item)
		{
			if (!isset($array[$item]))
			{
				return 0;
			}

			$count = min($count, $array[$item]);
		}

		return (int) $count;
	}

	//public static function count_recursivve()
	//{
	//
	//}

	/**
	 * @param array           $array
	 * @param int|string|null $property sums $item[$property] (via get()) instead of $item itself
	 *
	 * @return float|int
	 */
	public static function sum(array $array, int|string|null $property = null): float|int
	{
		$result = 0;

		foreach ($array as $item)
		{
			if ($property === null)
			{
				$result += (float) $item;
			}
			else
			{
				$result += (float) get($item, $property);
			}
		}

		return $result;
	}

	/**
	 * @return array $array with the first $n elements dropped
	 */
	public static function removeFirst(array $array, int $n = 1, bool $preserve_keys = false): array
	{
		if ($n <= 0)
		{
			return $array;
		}

		return array_slice($array, $n, null, $preserve_keys);
	}

	/**
	 * @return array $array with the last $n elements dropped
	 */
	public static function removeLast(array $array, int $n = 1, bool $preserve_keys = false): array
	{
		$array_size = count($array);

		if ($n >= $array_size)
		{
			return [];
		}

		return array_slice($array, 0, count($array) - $n, $preserve_keys);
	}

	/**
	 * Remove specified items from an array
	 *
	 * $haystack = array('a', 'b', 'f', 'r', 'b', 'v', 'r', 'b', 't', 'a');
	 * $needle = array('a', 'b');
	 *
	 * $result = arrays::remove_pairs($needle, $haystack); # array('f', 'r', 'v', 'r', 'b', 't');
	 *
	 * @param array $needle
	 * @param array $haystack
	 * @param int $count
	 *
	 * @return mixed
	 */
	public static function removePairs($needle, $haystack, $count = null)
	{
		# TODO: swap needle with haystack

		if ($count === null)
		{
			$count = static::count($needle, $haystack);
		}

		if (!$count)
		{
			return $haystack;
		}


		$result = $haystack;

		foreach ($needle as $item)
		{
			$index = array_keys($result, $item);

			for ($i = 0; $i < $count; $i++)
			{
				unset($result[$index[$i]]);
			}
		}

		return $result;
	}

	//public static function remove_empty($array)
	//{
	//	if (!$array)
	//	{
	//		return $array;
	//	}
	//
	//	return array_filter($array);
	//}

	/**
	 * @return array $array with any '' elements dropped and re-indexed
	 */
	public static function removeEmptyStrings(array $array): array
	{
		foreach ($array as $index => $value)
		{
			if ($value === '')
			{
				unset($array[$index]);
			}
		}

		if ($array)
		{
			$array = array_values($array);
		}

		return $array;
	}

	public static function removeNegative(array $array): array { foreach ($array as $key => $val) { if ($val < 0) { unset($array[$key]); } } return $array; }
	public static function removePositive(array $array): array { foreach ($array as $key => $val) { if ($val > 0) { unset($array[$key]); } } return $array; }

	public static function removeNonNegative(array $array): array { foreach ($array as $key => $val) { if ($val >= 0) { unset($array[$key]); } } return $array; }
	public static function removeNonPositive(array $array): array { foreach ($array as $key => $val) { if ($val <= 0) { unset($array[$key]); } } return $array; }

	public static function removeNull(array $array): array
	{
		# speed performance is not tested
		return array_filter($array, fn($value) => $value !== null);

		# old code
		//foreach ($array as $key => $val)
		//{
		//	if ($val === null)
		//	{
		//		unset($array[$key]);
		//	}
		//}
		//
		//return $array;
	}

	public static function removeValues(array $array, array $values): array
	{
		# TODO: here and below - allow non-array as second argument (?)

		return array_diff($array, $values);
	}

	public static function removeKeys(array $array, array $keys): array
	{
		return array_diff_key($array, array_flip($keys));
	}

	public static function keepValues(array $array, array $values): array
	{
		return array_intersect($array, $values);
	}

	public static function keepKeys(array $array, array $keys): array
	{
		return array_intersect_key($array, $keys);
	}

	public static function keys(array $items): array
	{
		return array_keys($items);
	}

	/**
	 * Generate all permutations of a given array
	 *
	 * $array = array('AAA', 'BBB', 'CCC');
	 *
	 * $result = arrays::permutation($array); # Array
	 *                                        # (
	 *                                        #     [0] => Array
	 *                                        #     (
	 *                                        #         [0] => AAA
	 *                                        #         [1] => BBB
	 *                                        #         [2] => CCC
	 *                                        #     )
	 *                                        #
	 *                                        #     [1] => Array
	 *                                        #     (
	 *                                        #         [0] => AAA
	 *                                        #         [1] => CCC
	 *                                        #         [2] => BBB
	 *                                        #     )
	 *                                        #
	 *                                        #     [2] => Array
	 *                                        #     (
	 *                                        #         [0] => BBB
	 *                                        #         [1] => CCC
	 *                                        #         [2] => AAA
	 *                                        #     )
	 *                                        #
	 *                                        #     [3] => Array
	 *                                        #     (
	 *                                        #         [0] => BBB
	 *                                        #         [1] => AAA
	 *                                        #         [2] => CCC
	 *                                        #     )
	 *                                        #
	 *                                        #     [4] => Array
	 *                                        #     (
	 *                                        #         [0] => CCC
	 *                                        #         [1] => AAA
	 *                                        #         [2] => BBB
	 *                                        #     )
	 *                                        #
	 *                                        #     [5] => Array
	 *                                        #     (
	 *                                        #         [0] => CCC
	 *                                        #         [1] => BBB
	 *                                        #         [2] => AAA
	 *                                        #     )
	 *                                        # )
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function permutation(array $array): array
	{
		$results = [];

		if (count($array) == 1)
		{
			$results[] = $array;
		}
		else
		{
			for ($i = 0; $i < count($array); $i++)
			{
				$first = array_shift($array);

				$subresults = static::permutation($array);

				array_push($array, $first);

				foreach ($subresults as $subresult)
				{
					$results[] = array_merge(array($first), $subresult);
				}
			}
		}

		return $results;
	}

	/**
	 * Find every subset of $array (values <= $needle only) whose values sum to $needle
	 *
	 * @param int|float $needle       target sum
	 * @param array     $array        candidate numbers
	 * @param bool      $return_first return only the first match instead of all matches
	 *
	 * @return array|null null if $return_first and nothing matched
	 */
	public static function combinations(int|float $needle, array $array, bool $return_first = false): ?array
	{
		foreach ($array as $key => $val)
		{
			if ($val > $needle)
			{
				unset($array[$key]);   # remove impossible options
			}
		}

		$array = array_values($array); # recreate the array

		rsort($array);                 # sort in reverse order

		$array_size = count($array);   # should be less than 20 (CPU limitations)

		$total = pow(2, $array_size);  # total number of possible combinations


		for ($result = [], $i = 0; $i < $total; $i++)
		{
			# permutation implementation - http://r.je/php-find-every-combination.html

			$sequence = [];

			for ($j = 0; $j < $array_size; $j++)
			{
				if (pow(2, $j) & $i)
				{
					$sequence[] = $array[$j];
				}
			}

			if (array_sum($sequence) == $needle)
			{
				if ($return_first)
				{
					return $sequence;
				}

				$result[] = $sequence;
			}
		}

		if ($return_first)
		{
			return null;
		}


		array_multisort(array_map('count', $result), SORT_ASC, $result);

		$result = array_unique($result, SORT_REGULAR);

		return array_filter($result);
	}

	/**
	 * Merges the given arrays, wrapping any non-array argument into a single-element array first
	 */
	public static function merge(...$arrays): array
	{
		foreach ($arrays as $index => $array)
		{
			if (!is_array($array))
			{
				$arrays[$index] = [$array];
			}
		}

		return array_merge(...$arrays);
	}

	/**
	 * @return array array_merge_recursive() over all arguments, or the raw argument list if only one was given
	 */
	public static function mergeRecursive(...$arrays): array
	{
		if (func_num_args() == 1)
		{
			return func_get_args();
		}

		return array_merge_recursive(...$arrays);
	}

	/**
	 * @return array array_replace_recursive() over all arguments, or the raw argument list if only one was given
	 */
	public static function combineRecursive(...$arrays): array
	{
		if (func_num_args() == 1)
		{
			return func_get_args();
		}

		return array_replace_recursive(...$arrays);
	}

	/**
	 * Recursively casts an array (and any nested arrays) to stdClass objects
	 *
	 * @param array|null $array
	 * @param bool       $escape_keys replace '-' in keys with '_' so they're valid property names
	 *
	 * @return object|null
	 */
	public static function toObject(array|null $array, bool $escape_keys = false): ?object
	{
		if ($array === null)
		{
			return null;
		}

		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$array[$key] = static::toObject($value, $escape_keys);
			}
		}

		$object = (object) $array;

		if ($escape_keys)
		{
			$search = ['-'];

			foreach ($object as $key => $val)
			{
				if (Strings::contains($key, $search))
				{
					$key_escaped = Strings::replace($key, $search, '_');

					$object->$key_escaped = $object->$key;

					unset($object->$key);
				}
			}
		}

		return $object;
	}

	/**
	 * Converts a (possibly nested) array to an XML string via toXMLRecursive()
	 *
	 * @param array  $items
	 * @param string $root           wraps the output in this root element
	 * @param string $numeric_prefix prefix used for numeric keys, which aren't valid XML tag names
	 * @param bool   $skip_header    strip the leading "<?xml ... ?>" declaration
	 *
	 * @return string
	 */
	public static function toXML(array $items, string $root = '', string $numeric_prefix = 'item_', bool $skip_header = true): string
	{
		if ($root !== '' and !Strings::startsWith($root, '<'))
		{
			$root = "<$root></$root>";
		}


		$xml = new SimpleXMLElement($root);

		static::toXMLRecursive($items, $xml, $numeric_prefix);


		$result = $xml->asXML();

		if ($skip_header)
		{
			$result = Strings::removeBetween($result, '<?', '?>', true, true);
			$result = trim($result);
		}

		return $result;
	}

	/**
	 * Recursively appends $items as child nodes of $xml, used internally by toXML()
	 */
	protected static function toXMLRecursive(array $items, SimpleXMLElement $xml, string $numeric_prefix = 'item_'): void
	{
		foreach ($items as $key => $value)
		{
			if (is_numeric($key))
			{
				$key = $numeric_prefix . $key; # dealing with <0/>..<n/> issues
			}
			if (is_array($value))
			{
				$subnode = $xml->addChild($key);

				static::toXMLRecursive($value, $subnode, $numeric_prefix);
			}
			else
			{
				$xml->addChild($key, htmlspecialchars($value));
			}
		}
	}

	/**
	 * @return array $items with every element lowercased
	 */
	public static function toLowercase(array $items): array
	{
		foreach ($items as $index => $item)
		{
			$items[$index] = Strings::toLowercase($item);
		}

		return $items;
	}

	public static function toInt(array $items): array
	{
		return array_map('intval', $items);
	}

	/**
	 * @param array           $items
	 * @param int|string|null $property measure $item[$property] (via get()) instead of $item itself
	 * @param int|null        $max_result caps the returned length
	 *
	 * @return int
	 */
	public static function maxLength(array $items, int|string|null $property = null, ?int $max_result = null): int
	{
		$max_length = 0;

		foreach ($items as $item)
		{
			if ($property !== null)
			{
				$item = get($item, $property);
			}

			$max_length = max($max_length, Strings::length($item));
		}

		if ($max_result !== null)
		{
			return min($max_length, $max_result);
		}

		return $max_length;
	}

	public static function maxLengthKeys($array, $max_result = null)
	{
		$keys = static::keys($array);

		return static::maxLength($keys, null, $max_result);
	}

	/**
	 * @param array           $items
	 * @param int|string|null $property   measure $item[$property] (via get()) instead of $item itself
	 * @param int|null        $min_result floors the returned length
	 *
	 * @return int
	 */
	public static function minLength(array $items, int|string|null $property = null, ?int $min_result = null): int
	{
		$min_length = INF;

		foreach ($items as $item)
		{
			if ($property !== null)
			{
				$item = get($item, $property);
			}

			$min_length = min($min_length, Strings::length($item));
		}

		if ($min_length === INF)
		{
			return 0;
		}

		if ($min_result !== null)
		{
			return max($min_length, $min_result);
		}

		return $min_length;
	}

	public static function minLengthKeys(array $array, ?int $min_result = null)
	{
		$keys = static::keys($array);

		return static::minLength($keys, null, $min_result);
	}

	/**
	 * @param array           $items
	 * @param int|string|null $property compares $item[$property] (via select()) instead of $item itself
	 *
	 * @return mixed
	 */
	public static function max(array $items, int|string|null $property = null): mixed
	{
		if ($property !== null)
		{
			$items = static::select($items, $property);
		}

		return max($items);
	}

	/**
	 * @param array           $items
	 * @param int|string|null $property compares $item[$property] (via select()) instead of $item itself
	 *
	 * @return mixed
	 */
	public static function min(array $items, int|string|null $property = null): mixed
	{
		if ($property !== null)
		{
			$items = static::select($items, $property);
		}

		return min($items);
	}

	public static function maxInt($items) { return static::extremum($items,  true, true); }
	public static function minInt($items) { return static::extremum($items, false, true); }

	public static function maxFloat($items) { return static::extremum($items,  true, false); }
	public static function minFloat($items) { return static::extremum($items, false, false); }

	/**
	 * Shared implementation for maxInt/minInt/maxFloat/minFloat: sanitizes each item as a number
	 * (dropping anything that isn't numeric) and tracks the running max/min
	 *
	 * @return int|float|null null if no item in $items was numeric
	 */
	private static function extremum($items, $max = true, $int = true)
	{
		     if ($max) { $result = -INF; }
		else           { $result =  INF; }

		foreach ($items as $item)
		{
			     if ($int) { $value = filter_var($item, FILTER_SANITIZE_NUMBER_INT); }
			else           { $value = filter_var($item, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); }

			if ($value !== false)
			{
				if (is_numeric($value))
				{
					     if ($int) { $value =   (int) $value; }
					else           { $value = (float) $value; }

					     if ($max) { $result = max($result, $value); }
					else           { $result = min($result, $value); }
				}
			}
		}

		if ($result === INF or $result === -INF)
		{
			return null;
		}

		return $result;
	}

	public static function add(array $items, int|float $number): array { foreach ($items as $index => $item) { $items[$index] = $item + $number; } return $items; }
	public static function sub(array $items, int|float $number): array { foreach ($items as $index => $item) { $items[$index] = $item - $number; } return $items; }
	public static function div(array $items, int|float $number): array { foreach ($items as $index => $item) { $items[$index] = $item / $number; } return $items; }
	public static function mul(array $items, int|float $number): array { foreach ($items as $index => $item) { $items[$index] = $item * $number; } return $items; }

	/**
	 * Renames array key $old to $new in place, keeping its value
	 *
	 * @param array      $item
	 * @param int|string $old
	 * @param int|string $new
	 */
	public static function rename(array &$item, int|string $old, int|string $new): void
	{
		$item[$new] = $item[$old];

		unset($item[$old]);
	}

	public static function reverse(array $items, $preserve_keys = false): array
	{
		return array_reverse($items, $preserve_keys);
	}

	public static function reverseKeys(array $items)
	{
		return array_reverse($items, true);
	}

	public static function flip(array $items)
	{
		return array_flip($items);
	}

	/**
	 * Pulls $property out of every item; supports Traversable objects in addition to plain arrays
	 *
	 * @param array|object $items
	 * @param int|string   $property
	 *
	 * @return array
	 */
	public static function select(array|object $items, int|string $property): array
	{
		if (is_object($items) and $items instanceof Traversable)
		{
			$result = [];

			foreach ($items as $item)
			{
				if (isset($item->{$property}))
				{
					$result[] = $item->{$property};
				}
			}

			return $result;
		}

		return array_column($items, $property);
	}

	/**
	 * Same as select(), but keyed by the value itself (deduplicating along the way)
	 *
	 * @param array|object $items
	 * @param int|string   $property
	 *
	 * @return array
	 */
	public static function selectUnique(array|object $items, int|string $property): array
	{
		if (is_object($items) and $items instanceof Traversable)
		{
			$result = [];

			foreach ($items as $item)
			{
				if (isset($item->{$property}))
				{
					$result[$item->{$property}] = $item->{$property};
				}
			}

			return $result;
		}

		return array_column($items, $property, $property);
	}

	/**
	 * Builds an associative array of $fields => value pulled out of $item via get()
	 *
	 * @param mixed        $item
	 * @param array|string $fields
	 * @param mixed        $default used for any field missing from $item
	 *
	 * @return array
	 */
	public static function fields(mixed $item, array|string $fields, mixed $default = null): array
	{
		$result = [];

		foreach ((array) $fields as $field)
		{
			$result[$field] = get($item, $field, $default);
		}

		return $result;
	}

	public static function unique($items)
	{
		return array_unique($items);
	}

	/**
	 * Re-keys $items: by their own value if $property is omitted, otherwise by $item[$property] (via get())
	 *
	 * @param iterable        $items
	 * @param int|string|null $property
	 *
	 * @return array
	 */
	public static function assoc(iterable $items, int|string|null $property = null): array
	{
		$result = [];

		if ($property === null)
		{
			foreach ($items as $item)
			{
				$result[$item] = $item;
			}
		}
		else
		{
			foreach ($items as $item)
			{
				$result[get($item, $property)] = $item;
			}
		}

		return $result;
	}

	/**
	 * Builds an associative array from two properties of each item: $property_key => $property_val
	 */
	public static function map(iterable $items, int|string $property_key, int|string $property_val): array
	{
		$result = [];

		foreach ($items as $item)
		{
			$result[get($item, $property_key)] = get($item, $property_val);
		}

		return $result;
	}

	/**
	 * Returns only the items whose $property equals $value; optionally removes matches from $items itself
	 */
	public static function where(array &$items, int|string $property, mixed $value, bool $strict = false, bool $unset = false): array
	{
		$result = [];

		foreach ($items as $index => $item)
		{
			$val = get($item, $property, null);

			     if ($strict) { $is_equal = $val === $value; }
			else              { $is_equal = $val ==  $value; }

			if ($is_equal)
			{
				$result[] = $item;

				if ($unset)
				{
					unset($items[$index]);
				}
			}
		}

		return $result;
	}

	/**
	 * Returns only the items whose $property does not equal $value; optionally removes matches from $items itself
	 */
	public static function whereNot(array &$items, int|string $property, mixed $value, bool $strict = false, bool $unset = false): array
	{
		$result = [];

		foreach ($items as $index => $item)
		{
			$val = get($item, $property, null);

			     if ($strict) { $is_equal = $val !== $value; }
			else              { $is_equal = $val !=  $value; }

			if ($is_equal)
			{
				$result[] = $item;

				if ($unset)
				{
					unset($items[$index]);
				}
			}
		}

		return $result;
	}


	public static function whereGreater       ($items, $property, $value) { return static::whereCustom($items, $property, $value, '>' ); }
	public static function whereGreaterOrEqual($items, $property, $value) { return static::whereCustom($items, $property, $value, '>='); }
	public static function whereLess          ($items, $property, $value) { return static::whereCustom($items, $property, $value, '<' ); }
	public static function whereLessOrEqual   ($items, $property, $value) { return static::whereCustom($items, $property, $value, '<='); }

	/**
	 * Shared implementation for whereGreater/whereGreaterOrEqual/whereLess/whereLessOrEqual
	 *
	 * @param string $sign one of '>', '>=', '<', '<='
	 */
	protected static function whereCustom(array $items, int|string $property, int|float $value, string $sign): array
	{
		$result = [];

		foreach ($items as $index => $item)
		{
			$item_value = (int) get($item, $property);

			switch ($sign)
			{
				case '>=': if ($item_value >= $value) { $result[$index] = $item; } break;
				case '>':  if ($item_value >  $value) { $result[$index] = $item; } break;
				case '<=': if ($item_value <= $value) { $result[$index] = $item; } break;
				case '<':  if ($item_value <  $value) { $result[$index] = $item; } break;
			}
		}

		return $result;
	}

	/**
	 * Removes (in place) every item whose $property equals $value
	 */
	public static function unsetWhere(array &$items, int|string $property, mixed $value, bool $strict = false): void
	{
		foreach ($items as $index => $item)
		{
			     if ($strict) { $is_equal = $item[$property] === $value; }
			else              { $is_equal = $item[$property]  == $value; }

			if ($is_equal)
			{
				unset($items[$index]);
			}
		}
	}

	public static function repeat($value, $count, $index_start = 0)
	{
		return array_fill($index_start, $count, $value);
	}

	public static function remove($items, $remove)
	{
		# remove by value

		return array_diff($items, $remove);
	}

	/**
	 * Keeps only the given keys/properties on every item (object or array) in $items, via fields()
	 *
	 * @return array[]
	 */
	public static function keepKeysBulk(array $items, array|string $keys): array
	{
		foreach ($items as $index => $item)
		{
			$items[$index] = self::fields($item, $keys);
		}

		return $items;
	}

	/**
	 * Removes the given keys/properties from every item (object or array) in $items, via fields()
	 *
	 * @return array[]
	 */
	public static function removeKeysBulk(array $items, array|string $keys): array
	{
		$keys = (array) $keys;

		foreach ($items as $index => $item)
		{
			$all_keys = is_object($item) ? array_keys(Objects::properties($item)) : self::keys($item);

			$items[$index] = self::fields($item, array_diff($all_keys, $keys));
		}

		return $items;
	}


	/**
	 * Recursively drops falsy values from $items, including nested arrays that become empty after pruning
	 */
	public static function removeEmpty(array $items): array
	{
		$items = array_filter($items);

		foreach ($items as $key => $val)
		{
			if (is_array($val))
			{
				$value = static::removeEmpty($val);

				if ($value)
				{
					$items[$key] = $value;
				}
				else
				{
					unset($items[$key]);
				}
			}
		}

		return $items;
	}

	/**
	 * Removes (in place) every item that starts with $substring
	 */
	public static function removeStartsWith(array $items, string $substring, bool $case_sensitive = true): array
	{
		foreach ($items as $index => $item)
		{
			if (Strings::startsWith($item, $substring, $case_sensitive))
			{
				unset($items[$index]);
			}
		}

		return $items;
	}

	/**
	 * NOTE: matches found in $array are only removed from a local copy of $values, $array itself is returned unchanged
	 */
	public static function keep(array $array, mixed $values, bool $strict = true): array
	{
		if (!is_array($values))
		{
			$values = [$values];
		}

		foreach ($array as $index => $item)
		{
			if ($strict)
			{
				foreach ($values as $key => $val)
				{
					if ($item === $val)
					{
						unset($values[$key]);
					}
				}
			}
			else
			{
				foreach ($values as $key => $val)
				{
					if ($item == $val)
					{
						unset($values[$key]);
					}
				}
			}
		}

		return $array;
	}

	/**
	 * Keeps only the items that are valid emails, optionally trimming/lowercasing them first
	 */
	public static function keepEmails(array $items, bool $trim = true, bool $to_lowercase = true): array
	{
		if ($trim)         { $items = static::trim        ($items); }
		if ($to_lowercase) { $items = static::toLowercase($items); }

		foreach ($items as $index => $email)
		{
			if (Strings::isEmail($email))
			{
				$items[$index] = $email;
			}
			else
			{
				unset($items[$index]);
			}
		}

		return $items;
	}

	/**
	 * Removes (in place) every item that does not start with $substring
	 */
	public static function keepStartWith(array $items, string $substring, bool $case_sensitive = true): array
	{
		foreach ($items as $index => $item)
		{
			if (!Strings::startsWith($item, $substring, $case_sensitive))
			{
				unset($items[$index]);
			}
		}

		return $items;
	}

	/**
	 * Removes (in place) every item whose key does not start with $substring
	 */
	public static function keepKeysStartWith(array $items, string $substring, bool $case_sensitive = true): array
	{
		foreach ($items as $index => $item)
		{
			if (!Strings::startsWith($index, $substring, $case_sensitive))
			{
				unset($items[$index]);
			}
		}

		return $items;
	}

	/**
	 * Walks $array following each key in $indexes in turn
	 *
	 * @return bool true if every nested key in $indexes exists
	 */
	public static function nestedExist(array $array, mixed $indexes): bool
	{
		$pointer = &$array;

		foreach ((array) $indexes as $index)
		{
			if (!is_array($pointer) or !self::keyExist($pointer, $index))
			{
				return false;
			}

			$pointer = &$pointer[$index];
		}

		return true;
	}

	/**
	 * Walks $array following each key in $indexes in turn
	 *
	 * @return mixed $default if any nested key in $indexes is missing
	 */
	public static function nestedGet(array $array, mixed $indexes, mixed $default = null): mixed
	{
		$pointer = &$array;

		foreach ((array) $indexes as $index)
		{
			if (!is_array($pointer) or !self::keyExist($pointer, $index))
			{
				return $default;
			}

			$pointer = &$pointer[$index];
		}

		return $pointer;
	}

	/**
	 * Same as nestedGet(), but returns a reference to the nested value (or to a throwaway null if not found)
	 * so the caller can assign through it
	 */
	public static function & nestedGetPointer(array $array, mixed $indexes): mixed
	{
		$pointer = &$array;

		foreach ((array) $indexes as $index)
		{
			if (!is_array($pointer) or !self::keyExist($pointer, $index))
			{
				$null = null;

				return $null;
			}

			$pointer = &$pointer[$index];
		}

		return $pointer;
	}

	/**
	 * @param mixed $array
	 * @param mixed $indexes
	 * @param mixed $default
	 *
	 * @return array [$is_exist, $result]
	 */
	public static function nestedGetVerbose($array, $indexes, $default = null)
	{
		$pointer = &$array;

		foreach ((array) $indexes as $index)
		{
			if (!is_array($pointer) or !self::keyExist($pointer, $index))
			{
				return [false, $default];
			}

			$pointer = &$pointer[$index];
		}

		return [true, $pointer];
	}

	/**
	 * Walks/creates the nested path described by $indexes inside $array (creating empty arrays along
	 * the way as needed) and assigns $value at that path when it was explicitly passed
	 */
	public static function nestedSet(array &$array, mixed $indexes, mixed $value = null): array
	{
		$pointer = &$array;

		foreach ((array) $indexes as $index)
		{
			if (!self::keyExist($pointer, $index))
			{
				$pointer[$index] = [];
			}

			$pointer = &$pointer[$index];
		}

		if (func_num_args() > 2)
		{
			$pointer = $value;
		}

		return $array;
	}

	/**
	 * Appends $value onto the array at the nested key-path, creating intermediate arrays as needed
	 *
	 * @param bool $unique skip appending if $value already exists at that path
	 */
	public static function nestedPush(array &$array, mixed $indexes, mixed $value, bool $unique = false): array
	{
		$pointer = &$array;

		foreach ((array) $indexes as $index)
		{
			if (!self::keyExist($pointer, $index))
			{
				$pointer[$index] = [];
			}

			$pointer = &$pointer[$index];
		}

		if ($unique and in_array($value, $pointer, true))
		{
			return $array;
		}

		$pointer[] = $value;

		return $array;
	}


	public static function diff($a, $b)
	{
		return array_diff($a, $b);
	}

	public static function common($a, $b)
	{
		return array_intersect($a, $b);
	}

	public static function implode($items, $glue = ', ')
	{
		return implode($glue, $items);
	}

	/**
	 * @param string   $items     the string to split
	 * @param string   $delimiter
	 * @param int|null $limit
	 *
	 * @return array
	 */
	public static function explode(string $items, string $delimiter = ', ', ?int $limit = null): array
	{
		if ($limit === null)
		{
			return explode($delimiter, $items);
		}

		return explode($delimiter, $items, $limit);
	}

	/**
	 * @return array $items split into chunks of $size
	 */
	public static function chunk(array $items, int $size, bool $preserve_keys = true): array
	{
		if ($preserve_keys)
		{
			return array_chunk($items, $size, true);
		}

		return array_chunk($items, $size);
	}

	/**
	 * Buckets $items by $item[$field] (via get()); within each bucket, items are re-keyed by
	 * $item[$field_index] when given, otherwise appended in order
	 */
	public static function group(array $items, int|string $field, int|string|null $field_index = null): array
	{
		$result = [];

		foreach ($items as $item)
		{
			$value = get($item, $field);

			if (!isset($result[$value]))
			{
				$result[$value] = [];
			}

			     if ($field_index === null) { $result[$value][]                         = $item; }
			else                            { $result[$value][get($item, $field_index)] = $item; }
		}

		return $result;
	}

	/**
	 * Recursively flattens nested arrays into a single flat list of scalar values
	 */
	public static function flat(array $values): array
	{
		$result = [];

		foreach ($values as $value)
		{
			if (is_array($value))
			{
				$result = array_merge($result, self::flat($value));
			}
			else
			{
				$result[] = $value;
			}
		}

		return $result;
	}

	/**
	 * Appends (and/or prepends) $string to every item, optionally skipping the last one
	 */
	public static function append(array $items, string $string, bool $append_after = true, bool $append_before = false, bool $skip_last = false): array
	{
		     if ($skip_last) { $last_index = static::keyLast($items); }
		else                 { $last_index = null;                       }

		foreach ($items as $index => $item)
		{
			if ($skip_last and $last_index == $index) { break; }

			if ($append_before) { $items[$index] = $string . $items[$index]; }
			if ($append_after)  { $items[$index] = $items[$index] . $string; }
		}

		return $items;
	}

	public static function appendBefore($items, $string, $skip_last = false) { return static::append($items, $string, false,  true, $skip_last); }
	public static function appendAfter ($items, $string, $skip_last = false) { return static::append($items, $string,  true, false, $skip_last); }
	public static function appendBoth  ($items, $string, $skip_last = false) { return static::append($items, $string,  true,  true, $skip_last); }

	/**
	 * Pads every item (or every $item[$property] when given) to $pad_length, defaulting to the
	 * longest item's length
	 */
	public static function pad(array $items, int|string|null $property = null, ?int $pad_length = null, string $pad_string = ' ', int $pad_direction = STR_PAD_RIGHT): array
	{
		if ($pad_length === null)
		{
			$pad_length = static::maxLength($items, $property);
		}

		foreach ($items as $index => $item)
		{
			if ($property !== null)
			{
				if (is_object($item))
				{
					$items[$index]->$property = Strings::pad($item->$property, $pad_length, $pad_string, $pad_direction);
				}
				else
				{
					$items[$index][$property] = Strings::pad($item[$property], $pad_length, $pad_string, $pad_direction);
				}
			}
			else
			{
				$items[$index] = Strings::pad($item, $pad_length, $pad_string, $pad_direction);
			}
		}

		return $items;
	}

	public static function padLeft ($items, $property = null, $pad_length = null, $pad_string = ' ') { return static::pad($items, $property, $pad_length, $pad_string, STR_PAD_LEFT);  }
	public static function padRight($items, $property = null, $pad_length = null, $pad_string = ' ') { return static::pad($items, $property, $pad_length, $pad_string, STR_PAD_RIGHT); }
	public static function padBoth ($items, $property = null, $pad_length = null, $pad_string = ' ') { return static::pad($items, $property, $pad_length, $pad_string, STR_PAD_BOTH);  }

	/**
	 * @return array $array re-keyed in natural, case-insensitive key order
	 */
	public static function natksort(array $array): array
	{
		$keys = array_keys($array);

		natcasesort($keys);


		$result = [];

		foreach ($keys as $key)
		{
			$result[$key] = $array[$key];
		}

		return $result;
	}

	public static function isAssoc($array)
	{
		return !array_is_list($array);
	}
}

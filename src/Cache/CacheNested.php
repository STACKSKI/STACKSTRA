<?php

namespace Stackstra\Cache;

use Stackstra\Exceptions\Exceptions;
use Stackstra\Types\Items;

use function Stackstra\get;

class CacheNested
{
	protected array $items = [];

	protected bool $is_pointer_valid;

	public function __construct(&$array = [])
	{
		if (func_num_args())
		{
			$this->items = &$array;
		}
	}

	public function & pointer($indexes, $default = null)
	{
		$this->is_pointer_valid = false;

		$pointer = &$this->items;

		foreach ((array) $indexes as $index)
		{
			if (!array_key_exists($index, $pointer))
			{
				# only variable references should be returned by reference

				return $default;
			}

			$pointer = &$pointer[$index];
		}

		$this->is_pointer_valid = true;

		return $pointer;
	}

	public function get($index = null, $default = null)
	{
		if (!func_num_args())
		{
			return $this->items;
		}


		$pointer = $this->pointer($index);

		if (!$this->is_pointer_valid)
		{
			return $default;
		}

		return $pointer;
	}

	public function getFlat()
	{
		return Items::flat($this->items);
	}

	public function getOrUpdate($index, $callback, $callback_values = [])
	{
		$pointer = $this->pointer($index);

		if (!$this->is_pointer_valid)
		{
			return $pointer;
		}


		$data = $callback(...$callback_values);

		$this->set($index, $data);

		return $data;
	}

	public function set($index, $value)
	{
		return Items::nestedSet($this->items, $index, $value);
	}

	public function create($index, $value = null)
	{
		return Items::nestedSet($this->items, $index, $value);
	}

	public function remove($index): bool
	{
		if (!$this->isExist($index))
		{
			return false;
		}


		# very sad hotfix because of https://www.php.net/manual/en/language.references.unset.php

		$indexes = (array) $index;

		switch (count($indexes))
		{
			case 1: unset($this->items[$indexes[0]]); break;
			case 2: unset($this->items[$indexes[0]][$indexes[1]]); break;
			case 3: unset($this->items[$indexes[0]][$indexes[1]][$indexes[2]]); break;
			case 4: unset($this->items[$indexes[0]][$indexes[1]][$indexes[2]][$indexes[3]]); break;
			case 5: unset($this->items[$indexes[0]][$indexes[1]][$indexes[2]][$indexes[3]][$indexes[4]]); break;

			default:
				Exceptions::error('unsupported amount of nested unsets');

				return false;
		}

		# TODO: https://www.php.net/manual/en/function.array-diff.php#68623 ?

		return true;
	}

	public function reset()
	{
		$this->items = [];
	}

	public function isPointerValid(): bool
	{
		return $this->is_pointer_valid === true;
	}

	public function isExist($index): bool
	{
		return Items::nestedExist($this->items, $index);
	}

	public function isArray($index): bool
	{
		$pointer = $this->pointer($index);

		if (!$this->is_pointer_valid)
		{
			return false;
		}

		return is_array($pointer);
	}

	public function isNull($index): bool
	{
		$pointer = $this->pointer($index);

		if (!$this->is_pointer_valid)
		{
			return false;
		}

		return is_null($pointer);
	}

	public function isEmpty($index): bool
	{
		$pointer = $this->pointer($index);

		if (!$this->is_pointer_valid)
		{
			return true;
		}

		return !$pointer;
	}

	public function arrayReset($index): bool
	{
		$pointer = &$this->pointer($index);

		if ($this->is_pointer_valid)
		{
			$pointer = [];

			return true;
		}

		return false;
	}

	public function arrayIsKeyExist($index, $key): bool
	{
		$pointer = $this->pointer($index);

		return $this->is_pointer_valid && (isset($pointer[$key]) || array_key_exists($key, $pointer));
	}

	public function arrayIsValueExist($index, $value): bool
	{
		$pointer = $this->pointer($index);

		return $this->is_pointer_valid and in_array($value, $pointer);
	}

	public function arrayRemoveValues($index, $values = []): int
	{
		$pointer = &$this->pointer($index);

		if (!$this->is_pointer_valid)
		{
			return 0;
		}

		$count = count($pointer);

		$pointer = Items::removeValues($pointer, (array) $values);

		return $count - count($pointer);
	}

	public function arrayRemoveKeys($index, $keys): bool
	{
		$pointer = &$this->pointer($index);

		if (!$this->is_pointer_valid or !is_array($pointer))
		{
			return false;
		}

		$pointer = Items::removeKeys($pointer, $keys);

		return true;
	}

	public function arrayKeepValues($index, $values): bool
	{
		$pointer = &$this->pointer($index);

		if (!$this->is_pointer_valid or !is_array($pointer))
		{
			return false;
		}

		$pointer = Items::keepValues($pointer, $values);

		return true;
	}

	public function arrayKeepKeys($index, $keys): bool
	{
		$pointer = &$this->pointer($index);

		if (!$this->is_pointer_valid or !is_array($pointer))
		{
			return false;
		}

		$pointer = Items::keepKeys($pointer, $keys);

		return true;
	}

	public function arrayGet($index, $key, $default = null)
	{
		$pointer = &$this->pointer($index);

		if (!$this->is_pointer_valid)
		{
			return $default;
		}

		return get($pointer, $key, $default);
	}

	public function arrayIsEqual($index, $value, $strict = false): bool
	{
		$pointer = &$this->pointer($index);

		if (!$this->is_pointer_valid)
		{
			return false;
		}

		     if ($strict) { return $pointer === $value; }
		else              { return $pointer  == $value; }
	}

	public function arrayPush($index, $value)
	{
		if (!$this->isExist($index))
		{
			$this->create($index, []);
		}

		$pointer = &$this->pointer($index);
		$pointer[] = $value;
	}

	public function arrayPushBulk($index, $values)
	{
		if (!$this->isExist($index))
		{
			$this->create($index, []);
		}


		$pointer = &$this->pointer($index);

		foreach ($values as $value)
		{
			$pointer[] = $value;
		}
	}

	public function arrayCount($index): int
	{
		$pointer = $this->pointer($index);

		if (!$this->is_pointer_valid or !is_array($pointer))
		{
			return 0;
		}

		return count($pointer);
	}

	public function arrayPop($index)
	{
		$pointer = &$this->pointer($index);

		if ($this->is_pointer_valid)
		{
			return array_pop($pointer);
		}

		return null;
	}

	public function arrayShift($index)
	{
		$pointer = &$this->pointer($index);

		if ($this->is_pointer_valid)
		{
			return array_shift($pointer);
		}

		return null;
	}

	public function arrayUnshift($index, $value)
	{
		$pointer = &$this->pointer($index);

		if ($this->is_pointer_valid)
		{
			return array_unshift($pointer, $value);
		}

		return null;
	}

	public function arrayFirst($index, $n = 1)
	{
		$pointer = $this->pointer($index);

		if ($this->is_pointer_valid)
		{
			return Items::first($pointer, $n);
		}

		return null;
	}

	public function arrayLast($index, $n = 1)
	{
		$pointer = $this->pointer($index);

		if ($this->is_pointer_valid)
		{
			return Items::last($pointer, $n);
		}

		return null;
	}
}
<?php

namespace Stackstra\Cache;

use Stackstra\Types\Items;
use RuntimeException;

use function Stackstra\get;
use function Stackstra\value;

class Cache
{
	protected array $items = [];

	protected ?int $limit = null;

	public function __construct(array $items = [], ?int $limit = null)
	{
		$this->items = $items;

		if ($limit !== null)
		{
			$this->limit = $limit;
		}
	}

	public static function make(array $items = [], ?int $limit = null): static
	{
		return new static(items: $items, limit: $limit);
	}

	public function push($value, bool $ignore_limit = false): bool
	{
		if (!$ignore_limit and $this->isFull())
		{
			return false;
		}

		$this->items[] = $value;

		return true;
	}

	public function pushBulk(array $values, bool $ignore_limit = false)
	{
		$result = true;

		foreach ($values as $value)
		{
			$status = $this->push($value, $ignore_limit);

			$result = $result && $status;
		}

		return $result;
	}

	public function pop()
	{
		if ($this->isEmpty())
		{
			return null;
		}

		return array_pop($this->items);
	}

	public function unshift($value): bool
	{
		if ($this->isFull())
		{
			return false;
		}

		array_unshift($this->items, $value);

		return true;
	}

	public function shift()
	{
		if ($this->isEmpty())
		{
			return null;
		}

		return array_shift($this->items);
	}

	public function unique()
	{
		$this->items = array_unique($this->items);
	}

	public function removeValue($value)
	{
		if (($key = array_search($value, $this->items)) !== false)
		{
			unset($this->items[$key]);
		}
	}

	public function remove($key)
	{
		if ($this->isExist($key))
		{
			unset($this->items[$key]);
		}
	}

	public function reset(): self
	{
		$this->items = [];

		return $this;
	}

	public function get($key = null, $default = null)
	{
		if (func_num_args())
		{
			return get($this->items, $key, $default);
		}

		return $this->items;
	}

	public function getOrFail($key)
	{
		if (!$this->isExist($key)) {
			throw new RuntimeException("the following array key does not exist: `$key`");
		}

		return $this->items[$key];
	}

	public function hit($key = null, $default = null)
	{
		if (!$this->isExist($key))
		{
			$this->set($key, value($default));
		}

		return $this->get($key);
	}

	public function getFirst($n = 1, $offset = 0) { return Items::first($this->items, $n, $offset); }
	public function getLast ($n = 1, $offset = 0) { return Items::last ($this->items, $n, $offset); }

	public function count(): int
	{
		return count($this->items);
	}

	public function set($key, $val)
	{
		$this->items[$key] = $val;
	}

	public function setIfNotExist($key, $val)
	{
		if (!$this->isExist($key))
		{
			$this->set($key, value($val));
		}

		return $this->get($key);
	}

	public function setBulk(array $array)
	{
		$this->items = $array + $this->items;
	}

	public function copy($from, $to)
	{
		$this->set($to, $this->get($from));
	}

	public function copyIfNotExist($from, $to)
	{
		if ($this->isExist($from) and !$this->isExist($to))
		{
			$this->copy($from, $to);
		}
	}

	public function isExistValue($value)
	{
		return in_array($value, $this->items);
	}

	public function isExist($key): bool
	{
		return array_key_exists($key, $this->items);
	}

	public function isExistAll(array $keys)
	{
		foreach ($keys as $key)
		{
			if (!$this->isExist($key))
			{
				return false;
			}
		}

		return true;
	}

	public function isEmpty(): bool
	{
		return $this->count() <= 0;
	}

	public function isFull(): bool
	{
		if ($this->limit === null)
		{
			return false;
		}

		return $this->count() >= $this->limit;
	}
}

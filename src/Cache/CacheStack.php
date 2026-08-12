<?php

namespace Stackstra\Cache;

class CacheStack
{
	protected Cache $cache;

	protected ?int $limit = null;

	public function __construct(?int $limit = null)
	{
		if (func_num_args())
		{
			$this->limit = (int) $limit;
		}

		$this->reset();
	}

	public function reset()
	{
		$this->cache = new Cache(limit: $this->limit);
	}

	public function add($value, $ignore_limit = false): bool
	{
		return $this->cache->push($value, $ignore_limit);
	}

	public function remove()
	{
		return $this->cache->pop();
	}

	public function removeBulk($n): array
	{
		$result = [];

		for ($i = 0; $i < $n; $i++)
		{
			$result[] = $this->cache->pop();
		}

		return $result;
	}

	public function removeUntil($value): bool
	{
		while (!$this->isEmpty())
		{
			if ($this->remove() == $value)
			{
				return true;
			}
		}

		return false;
	}

	public function get()            { return $this->cache->get();        }
	public function getFirst($n = 1) { return $this->cache->getLast ($n); }
	public function getLast ($n = 1) { return $this->cache->getFirst($n); }

	public function exist($val): bool { return $this->cache->isExistValue($val); }

	public function isTop   ($val): bool { return $this->getFirst() == $val; }
	public function isBottom($val): bool { return $this->getLast()  == $val; }

	public function count(): int { return $this->cache->count(); }

	public function isEmpty(): bool { return $this->cache->isEmpty(); }
	public function isFull():  bool { return $this->cache->isFull();  }
}

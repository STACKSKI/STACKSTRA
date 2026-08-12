<?php

namespace Stackstra\Cache;

class CachePipe
{
	protected Cache $cache;

	public function __construct($limit = null)
	{
		$this->cache = new Cache(limit: $limit);
	}

	public function add($value, $force = false): bool { return $this->cache->push($value, $force); }

	public function take() { return $this->cache->shift(); }

	public function get()            { return $this->cache->get();        }
	public function getFirst($n = 1) { return $this->cache->getFirst($n); }
	public function getLast ($n = 1) { return $this->cache->getLast ($n); }

	public function isFirst($val): bool { return $this->getFirst() == $val; }
	public function isLast ($val): bool { return $this->getLast()  == $val; }

	public function count(): int { return $this->cache->count(); }

	public function isEmpty(): bool { return $this->cache->isEmpty(); }
	public function isFull():  bool { return $this->cache->isFull();  }
}

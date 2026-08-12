<?php
//
//namespace Stackstra\Map;
//
//use Closure;
//
//class Map
//{
//	protected array $map;
//
//	public function __construct(array $map = [])
//	{
//		$this->map = $map;
//	}
//
//	/**
//	 * @param string $key
//	 * @param mixed  $value
//	 *
//	 * @return $this
//	 */
//	public function add(string $key, mixed $value): self
//	{
//		$this->map[$key] = $value;
//
//		return $this;
//	}
//
//	/**
//	 * @param array $values
//	 *
//	 * @return $this
//	 */
//	public function merge(array $values): self
//	{
//		$this->map += $values;
//
//		return $this;
//	}
//
//	public function hit(string $key, Closure $callback)
//	{
//		$key = static::keyFormat($key, $this->imlode_char);
//
//		if (!$this->isExist($key))
//		{
//			$this->map[$key] = value($callback);
//		}
//
//		return $this->map[$key];
//	}
//
//	/**
//	 * @param string $key
//	 *
//	 * @return bool
//	 */
//	public function isExist(string $key): bool
//	{
//		return array_key_exists($key, $this->map);
//	}
//}

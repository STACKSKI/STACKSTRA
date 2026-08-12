<?php

namespace Stackstra\Etc;

use Stackstra\Traits\Singleton;

class Timer
{
	use Singleton;

	protected float $started;

	public function __construct(bool $autostart = true)
	{
		if ($autostart)
		{
			$this->start();
		}
	}

	public static function init()
	{
		return (new static())->start();
	}

	public function start(): self
	{
		$this->started = microtime(true);

		return $this;
	}

	public function diff(): float
	{
		return microtime(true) - $this->started;
	}

	public function diffMilliseconds($precision = null): float
	{
		$diff = self::diff();

		return Convert::secondsToMilliseconds($diff, $precision);
	}
}

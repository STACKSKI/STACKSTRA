<?php

namespace Stackstra\Curl;

use Stackstra\Etc\Convert;
use Stackstra\Types\Items;

class CurlThrottle
{
	protected CurlOptions $options;

	/** @var float[] */
	protected array $timestamps = []; # stores connection timestamp

	public function __construct(CurlOptions $options)
	{
		$this->options = $options;
	}

	public function slots(): ?int
	{
		return $this->options->throttle_slots();
	}

	public function interval(): ?int
	{
		return $this->options->throttle_interval();
	}

	public function isActive(): bool
	{
		return $this->slots() && $this->interval();
	}

	protected static function now(?float $now = null)
	{
		if ($now === null)
		{
			$now = microtime(true);
		}

		return $now;
	}

	public function slotsUsed(): int
	{
		return count($this->timestamps);
	}

	public function slotsAvailable(): int
	{
		return $this->slots() - self::slotsUsed();
	}

	public function hasSlotsAvailable(): bool
	{
		return self::slotsAvailable() > 0;
	}

	public function intervalStartedAt(): ?float
	{
		return Items::first($this->timestamps) ?? null;
	}

	public function intervalNextAt(): ?float
	{
		$started_at = self::intervalStartedAt();

		$interval = self::interval();

		if ($started_at === null || $interval === null)
		{
			return null;
		}

		return $started_at + $this->interval();
	}

	public function intervalReset()
	{
		$this->timestamps = [];
	}

	public function log(?float $now = null)
	{
		$now = self::now($now);

		$this->timestamps[] = $now;
	}

	public function trigger(int $count_tasks_incomplete, ?float $now = null): int
	{
		if (!$this->isActive())
		{
			return min($this->options->threads(), $count_tasks_incomplete);
		}


		$now = static::now($now);

		$interval_next_at = $this->intervalNextAt();

		if ($interval_next_at !== null && $now >= $interval_next_at)
		{
			$this->intervalReset();
		}

		if (!$this->hasSlotsAvailable())
		{
			$sleep = $this->intervalNextAt() - $now;
			$sleep = max(0, $sleep);

			static::sleep($sleep);

			$this->intervalReset();
		}

		return min($this->options->threads(), $count_tasks_incomplete, $this->slotsAvailable());
	}

	protected static function sleep(float $length): void
	{
		$microseconds = (int) Convert::secondsToMicroseconds($length);

		usleep($microseconds);
	}
}

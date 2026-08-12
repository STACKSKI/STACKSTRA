<?php

namespace Stackstra\Curl;

use Stackstra\Types\Items;

class CurlTasks
{
	/** @var CurlTask[] */
	protected array $tasks = [];

	/** @var CurlTask[] */
	protected array $tasks_incomplete = [];

	/** @var CurlTask[] */
	protected array $tasks_aborted = [];

	protected CurlOptions $options;

	public function __construct(CurlOptions $options)
	{
		$this->options = $options;
	}

	public function add(CurlTask $task)
	{
		$task->id_internal = static::internalID();

		$this->tasks           [$task->id_internal] = $task;
		$this->tasks_incomplete[$task->id_internal] = $task;
	}

	protected static function internalID()
	{
		static $i = 0;

		return ++$i;
	}

	public function count():           int { return count($this->tasks);            }
	public function countIncomplete(): int { return count($this->tasks_incomplete); }
	public function countAborted():    int { return count($this->tasks_aborted);    }

	public function countComplete(): int
	{
		return $this->count() - $this->countIncomplete();
	}

	public function hasIncomplete(): bool
	{
		return $this->countIncomplete() > 0;
	}

	public function hasAborted(): bool
	{
		return $this->countAborted() > 0;
	}

	/**
	 * @return CurlTask[]
	 */
	public function get(): array
	{
		return $this->tasks;
	}

	/**
	 * @param int|null $limit
	 *
	 * @return CurlTask[]
	 */
	public function getIncomplete(?int $limit = null): array
	{
		return Items::first($this->tasks_incomplete, length: $limit, preserve_keys: false, force_array: true);
	}

	public function getComplete(): array
	{
		return array_filter($this->tasks, fn($task) => !isset($this->tasks_incomplete[$task->id_internal]));
	}

	/**
	 * @param int|null $limit
	 *
	 * @return CurlTask[]
	 */
	public function getAborted(?int $limit = null): array
	{
		return Items::first($this->tasks_aborted, length: $limit, preserve_keys: false, force_array: true);
	}

	public function complete(int $internalID): self
	{
		unset($this->tasks_incomplete[$internalID]);

		return $this;
	}

	public function abort(int $internalID): self
	{
		$this->tasks_aborted[$internalID] = $this->tasks[$internalID];

		unset($this->tasks_incomplete[$internalID]);

		return $this;
	}

	public function abortIncomplete(): self
	{
		$this->tasks_aborted = $this->tasks_aborted + $this->tasks_incomplete;

		$this->tasks_incomplete = [];

		return $this;
	}
}

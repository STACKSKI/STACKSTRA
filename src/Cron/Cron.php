<?php

namespace Stackstra\Cron;

use Stackstra\Etc\Convert;
use Stackstra\Types\Floats;

class Cron
{
	protected $sleep = 1;  # max delay between tasks
	protected $sleep_last; # timestamp (uint32)

	protected $tasks         = []; # name (string) => interval (numeric, seconds)
	protected $tasks_history = []; # name (string) => timestamp (uint32)
	protected $tasks_timer   = []; # name (string) => execution time (numeric, seconds)

	public function __construct($tasks = [])
	{
		static::add_tasks($tasks);
	}

	public function addTask($task, $interval)
	{
		$this->tasks[$task] = $interval;
	}

	public function add_tasks($tasks)
	{
		foreach ($tasks as $task => $interval)
		{
			static::addTask($task, $interval);
		}
	}

	public function set_sleep($value)
	{
		$this->sleep = (int) $value;
	}

	public function sleep()
	{
		     if ($this->sleep_last) { $sleep = $this->sleep - (static::timestamp() - $this->sleep_last); }
		else                        { $sleep = 0; }

		     if (Floats::isGreater ($sleep, $this->sleep)) { $sleep = $this->sleep; }
		else if (Floats::isNegative($sleep))               { $sleep = 0; }

		$sleep_microseconds = Convert::secondsToMicroseconds($sleep);
		$sleep_microseconds = Floats::ceil($sleep_microseconds);

		usleep($sleep_microseconds);

		$this->sleep_last = static::timestamp();
	}

	public function tasks()
	{
		$tasks = [];

		$now = static::timestamp();

		foreach ($this->tasks as $task => $interval)
		{
			if (static::is_task_should_be_launched($task, $now))
			{
				$tasks[] = $task;
			}
		}

		return $tasks;
	}

	public function is_task_should_be_launched($task, $timestamp = null)
	{
		if (!isset($this->tasks_history[$task]))
		{
			return true;
		}


		if ($timestamp === null)
		{
			$timestamp = static::timestamp();
		}

		return Floats::isGreaterOrEqual($timestamp, $this->tasks_history[$task] + $this->tasks[$task]);
	}

	public function task_finished($task, $timestamp = null)
	{
		if ($timestamp === null)
		{
			$timestamp = static::timestamp();
		}

		$this->tasks_history[$task] = $timestamp;
	}

	public function timestamp()
	{
		return microtime(true);
	}
}
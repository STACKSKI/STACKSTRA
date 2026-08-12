<?php

namespace Stackstra\Curl;

use Stackstra\Exceptions\Exceptions;
use Stackstra\Types\Items;

class Curl
{
	readonly public CurlOptions  $options;
	readonly public CurlTasks    $tasks;
	readonly public CurlThrottle $throttle;

	public function __construct(array $options = [])
	{
		$this->options  = new CurlOptions($options);
		$this->tasks    = new CurlTasks($this->options);
		$this->throttle = new CurlThrottle($this->options);
	}

	/**
	 * @param $task string|CurlTask|string[]|CurlTask[]
	 * @param $id   null|int|string
	 *
	 * @return $this
	 */
	public function add(string|array|CurlTask $task, $id = null)
	{
		if (is_array($task))
		{
			foreach ($task as $id => $item)
			{
				static::add($item, $id);
			}
		}
		else if (is_object($task))
		{
			if ($id !== null)
			{
				$task->id = $id;
			}

			static::addTask($task);
		}
		else
		{
			static::addURL($task, $id);
		}

		return $this;
	}

	/**
	 * @param $url string
	 * @param $id  null|int|float|string
	 *
	 * @return $this
	 */
	public function addURL(string $url, null|int|float|string $id = null)
	{
		$task = new CurlTask($url, $id);

		$this->tasks->add($task);

		return $this;
	}

	/**
	 * @param $task CurlTask
	 *
	 * @return $this
	 */
	public function addTask(CurlTask $task)
	{
		$this->tasks->add($task);

		return $this;
	}

	/**
	 * @param CurlEvents|null $events
	 *
	 * @return CurlResponseList
	 */
	public function query(?CurlEvents $events = null): CurlResponseList
	{
		$response_list = new CurlResponseList();

		if (!$this->tasks->count())
		{
			Exceptions::warning('task list is empty');

			return $response_list;
		}


		$multi_handle = curl_multi_init();

		while ($this->tasks->hasIncomplete())
		{
			$limit = $this->throttle->trigger($this->tasks->countIncomplete());

			$tasks = $this->tasks->getIncomplete(limit: $limit);

			if (!$tasks)
			{
				Exceptions::error('unexpected code state - no tasks available');
			}


			foreach ($tasks as $task)
			{
				$this->throttle->log();

				$task->connection_timestamp = microtime(true);
				$task->connection_attempts++;

				$task_handle = $task->getHandle();

				curl_multi_add_handle($multi_handle, $task_handle);
			}

			do
			{
				curl_multi_exec($multi_handle, $running);

				curl_multi_select($multi_handle);
			}
			while ($running > 0);


			$max_attempts = $this->options->max_attempts();

			foreach ($tasks as $index => $task)
			{
				$response = new CurlResponse($task);

				curl_multi_remove_handle($multi_handle, $task->getHandle());

				$isOK = $response->isHttpCodeOK();

				if ($isOK || ($response->attempts >= $max_attempts))
				{
					$task->close();

					$this->tasks->complete($task->id_internal);

					if ($events)
					{
						$returns = [];

						$event_arguments = new CurlEventArguments(curl: $this, task: $task, response: $response, events: $events, response_list: $response_list);

						     if ($isOK) { $returns[] = $events->triggerOnSuccess($event_arguments); }
						else            { $returns[] = $events->triggerOnError  ($event_arguments); }

						$returns[] = $events->triggerOnComplete($event_arguments);

						if (in_array(false, $returns, strict: true))
						{
							$this->tasks->abort($task->id_internal);

							$this->tasks->abortIncomplete();

							$events->triggerOnAbort($event_arguments);

							foreach (Items::first($tasks, length: null, offset: $index + 1, preserve_keys: false) as $task_to_abort)
							{
								/** @var CurlTask $task_to_abort */
								curl_multi_remove_handle($multi_handle, $task_to_abort->getHandle());

								$task_to_abort->close();
							}

							break 2;
						}
					}

					if ($this->options->remember_responses())
					{
						$response_list->add($response);
					}
				}
			}
		}

		curl_multi_close($multi_handle);


		if ($events)
		{
			$event_arguments = new CurlEventArguments(curl: $this, task: null, response: null, events: $events, response_list: $response_list);

			$events->triggerOnCompleteAll($event_arguments);
		}

		return $response_list;
	}

	/**
	 * @en CURL file_get_contents() equivalent with timeout settings
	 *
	 * $html = curl::file_get_contents('http://site.com', 3);
	 *
	 * @param string $url
	 * @param int $timeout
	 *
	 * @return bool|string
	 */
	public static function fileGetContents($url, $timeout = 30)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_AUTOREFERER,    true);
		curl_setopt($ch, CURLOPT_HEADER,         0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL,            $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

		$data = curl_exec($ch);

		return $data;
	}
}

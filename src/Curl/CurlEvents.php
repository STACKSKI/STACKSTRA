<?php

namespace Stackstra\Curl;

use Closure;

use Stackstra\Etc\Reflection;
use Stackstra\Exceptions\Exceptions;

class CurlEvents
{
	const string ON_COMPLETE_ALL = 'onCompleteAll';
	const string ON_COMPLETE     = 'onComplete';
	const string ON_SUCCESS      = 'onSuccess';
	const string ON_ERROR        = 'onError';
	const string ON_ABORT        = 'onAbort';
	const string ON_ABORT_ALL    = 'onAbortAll';

	/** @var Closure[] */
	protected array $callbacks = [];

	/**
	 * @var array<string, int>
	 */
	protected array $counters = [];

	public static function make(...$callbacks): static
	{
		$instance = new static();
		$instance->setBulk($callbacks);

		return $instance;
	}

	/**
	 * @return string[]
	 */
	public static function eventNames(): array
	{
		static $events;

		if ($events === null)
		{
			$constants = Reflection::getConstantsPublic(static::class);

			$events = array_combine($constants, $constants);
		}

		return $events;
	}

	public function hasEventName(string $name): bool
	{
		return array_key_exists($name, self::eventNames());
	}


	/**
	 * @param Closure(CurlEventArguments): mixed $closure
	 */
	public function setOnCompleteAll(Closure $closure): self
	{
		return static::set(static::ON_COMPLETE_ALL, $closure);
	}

	/**
	 * @param Closure(CurlEventArguments): mixed $closure
	 */
	public function setOnComplete(Closure $closure): self
	{
		return static::set(static::ON_COMPLETE, $closure);
	}

	/**
	 * @param Closure(CurlEventArguments): mixed $closure
	 */
	public function setOnSuccess(Closure $closure): self
	{
		return static::set(static::ON_SUCCESS, $closure);
	}

	/**
	 * @param Closure(CurlEventArguments): mixed $closure
	 */
	public function setOnError(Closure $closure): self
	{
		return static::set(static::ON_ERROR, $closure);
	}

	/**
	 * @param Closure(CurlEventArguments): mixed $closure
	 */
	public function setOnAbort(Closure $closure): self
	{
		return static::set(static::ON_ABORT, $closure);
	}

	/**
	 * @param Closure(CurlEventArguments): mixed $closure
	 */
	public function setOnAbortAll(Closure $closure): self
	{
		return static::set(static::ON_ABORT_ALL, $closure);
	}

	protected function set(string $key, Closure $closure): self
	{
		if (!$this->hasEventName($key))
		{
			Exceptions::error("unexpected callback name: `$key`");
		}

		$this->callbacks[$key] = $closure;

		return $this;
	}

	protected function setBulk(array $values): self
	{
		foreach ($values as $name => $callback)
		{
			$this->set($name, $callback);
		}

		return $this;
	}

	public function triggerOnCompleteAll(...$arguments): mixed { return static::trigger(static::ON_COMPLETE_ALL, $arguments); }
	public function triggerOnComplete   (...$arguments): mixed { return static::trigger(static::ON_COMPLETE,     $arguments); }
	public function triggerOnSuccess    (...$arguments): mixed { return static::trigger(static::ON_SUCCESS,      $arguments); }
	public function triggerOnError      (...$arguments): mixed { return static::trigger(static::ON_ERROR,        $arguments); }
	public function triggerOnAbort      (...$arguments): mixed { return static::trigger(static::ON_ABORT,        $arguments); }
	public function triggerOnAbortAll   (...$arguments): mixed { return static::trigger(static::ON_ABORT_ALL,    $arguments); }

	protected function trigger(string $event, array $arguments = []): mixed
	{
		if ($this->has($event))
		{
			$this->counters[$event] = ($this->counters[$event] ?? 0) + 1;

			return $this->callbacks[$event](...$arguments);
		}

		return null;
	}

	public function hasOnCompleteAll(): bool { return static::has(self::ON_COMPLETE_ALL); }
	public function hasOnComplete   (): bool { return static::has(self::ON_COMPLETE);     }
	public function hasOnSuccess    (): bool { return static::has(self::ON_SUCCESS);      }
	public function hasOnError      (): bool { return static::has(self::ON_ERROR);        }
	public function hasOnAbort      (): bool { return static::has(self::ON_ABORT);        }
	public function hasOnAbortAll   (): bool { return static::has(self::ON_ABORT_ALL);    }

	protected function has(string $key): bool
	{
		return isset($this->callbacks[$key]);
	}


	public function unsetOnCompleteAll(): ?Closure { return static::unset(self::ON_COMPLETE_ALL); }
	public function unsetOnComplete   (): ?Closure { return static::unset(self::ON_COMPLETE);     }
	public function unsetOnSuccess    (): ?Closure { return static::unset(self::ON_SUCCESS);      }
	public function unsetOnError      (): ?Closure { return static::unset(self::ON_ERROR);        }
	public function unsetOnAbort      (): ?Closure { return static::unset(self::ON_ABORT);        }
	public function unsetOnAbortAll   (): ?Closure { return static::unset(self::ON_ABORT_ALL);    }

	protected function unset(string $event): ?Closure
	{
		if (!isset($this->callbacks[$event]))
		{
			return null;
		}

		$callback = $this->callbacks[$event];

		unset($this->callbacks[$event]);

		return $callback;
	}

	public function counters(): array
	{
		return $this->counters;
	}

	public function countersReset(): self
	{
		$this->counters = [];

		return $this;
	}

	public function count(string $event): int
	{
		return $this->counters[$event] ?? 0;
	}

	public function countCompleteAll(): int { return $this->count(self::ON_COMPLETE_ALL); }
	public function countComplete():    int { return $this->count(self::ON_COMPLETE);     }
	public function countSuccess():     int { return $this->count(self::ON_SUCCESS);      }
	public function countError():       int { return $this->count(self::ON_ERROR);        }
	public function countAbort():       int { return $this->count(self::ON_ABORT);        }
}

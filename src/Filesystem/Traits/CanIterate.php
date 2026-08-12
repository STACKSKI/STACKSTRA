<?php

namespace Stackstra\Filesystem\Traits;

trait CanIterate
{
	protected ?array $directories = null;

	public function rewind(): void
	{
		if ($this->directories === null)
		{
			$this->directories = $this->directories();
		}

		reset($this->directories);
	}

	public function current(): mixed
	{
		return current($this->directories);
	}

	public function key(): mixed
	{
		return key($this->directories);
	}

	public function next(): void
	{
		next($this->directories);
	}

	public function valid(): bool
	{
		return key($this->directories) !== null;
	}

	abstract protected function directories(): array;
}

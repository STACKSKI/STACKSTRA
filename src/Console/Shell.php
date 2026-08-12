<?php

namespace Stackstra\Console;

use Stackstra\Types\Chars;

class Shell
{
	protected string $command = '';

	protected array $options   = [];
	protected array $arguments = [];

	protected array $output = [];

	protected ?int $result_code = null;

	public function __construct($command = '')
	{
		static::command($command);
	}

	/**
	 * @param string   $command
	 * @param string[] $options
	 *
	 * @return self
	 */
	public static function run(string $command, array $options = []): self
	{
		return (new static($command))->options($options)->exec();
	}

	public function command($command): self
	{
		$this->command = $command;

		return $this;
	}

	public function options(array $options = []): self
	{
		foreach ($options as $option => $argument)
		{
			self::option($option, $argument);
		}

		return $this;
	}

	public function option($option, $argument = null): self
	{
		$this->options[$option] = $argument;

		return $this;
	}

	public function argument($argument): self
	{
		$this->arguments[] = $argument;

		return $this;
	}

	public function query(): string
	{
		$query = $this->command . ' ';

		foreach ($this->options as $option => $argument)
		{
			if ($argument === null)
			{
				$query .= $option . ' ';
			}
			else
			{
				$query .= $option . ' ' . static::escape($argument) . ' ';
			}
		}

		foreach ($this->arguments as $argument)
		{
			$query .= static::escape($argument) . ' ';
		}

		$query = Chars::removeLast($query);

		return $query;
	}

	public static function escape($var): string
	{
		if (is_numeric($var))
		{
			return $var;
		}

		return escapeshellarg($var);
	}

	public function exec(): self
	{
		exec(static::query(), $this->output, $this->result_code);

		return $this;
	}

	public function output(bool $to_string = true): array|string
	{
		if ($to_string)
		{
			return implode(PHP_EOL, $this->output);
		}

		return $this->output;
	}

	public function code(): ?int
	{
		return $this->result_code;
	}

	public function reset()
	{
		$this->command = '';

		$this->options   = [];
		$this->arguments = [];
		$this->output    = [];

		$this->result_code = null;
	}
}

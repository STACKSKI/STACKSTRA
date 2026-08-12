<?php

namespace Stackstra\Console;

use Closure;

use Stackstra\Cache\Cache;

class Prompt
{
	const string OPTION_QUIT = 'q';

	protected readonly Cache $options;

	protected string|null $option_quit;

	protected array $callbacks = [];

	# CLI can provide answers to questions through command arguments to skip "readline" calls
	protected static array $arguments = [];

	public function __construct(array $options = [], ?string $option_quit = self::OPTION_QUIT)
	{
		$this->options = new Cache();

		$this->addBulk($options);

		$this->option_quit = $option_quit;
	}

	public static function arguments(array $arguments)
	{
		static::$arguments = $arguments;
	}

	public static function make(array $options = [], ?string $option_quit = self::OPTION_QUIT)
	{
		return new self(options: $options, option_quit: $option_quit);
	}

	public function add(string $index, mixed $option, Closure $callback, array $arguments = []): self
	{
		$this->options->set($index, [$option, $callback, $arguments]);

		return $this;
	}

	public function addBulk(array $items): self
	{
		foreach ($items as $item)
		{
			[$index, $value, $callback, $arguments] = $item + [null, null, null, []];

			$this->add($index, $value, $callback, $arguments);
		}

		return $this;
	}

	public function optionQuit(?string $value = null): self|string|null
	{
		if ($value === null)
		{
			return $this->option_quit;
		}

		$this->option_quit = $value;

		return $this;
	}

	public function run()
	{
		$option = null;

		for (;;)
		{
			if ($option !== null)
			{
				Console::write("< $option does not exist", bold: true);
			}


			Console::lines();

			foreach ($this->options->get() as $i => [$label, $callback])
			{
				Console::write("[$i]: $label");
			}

			if ($this->option_quit !== null)
			{
				Console::write("[$this->option_quit]: Quit");
			}

			Console::write();

			if (static::$arguments)
			{
				$option = array_shift(static::$arguments);

				Console::write("> $option [CLI args]");
			}
			else
			{
				$option = Console::read('> ');
			}

			if ($this->options->isExist($option))
			{
				[$label, $callback, $arguments] = $this->options->get($option);

				$callback(...$arguments);

				break;
			}

			if ($option === $this->option_quit)
			{
				break;
			}
		}
	}
}

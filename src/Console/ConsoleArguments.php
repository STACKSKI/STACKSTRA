<?php

namespace Stackstra\Console;

use Stackstra\Types\Strings;

use function Stackstra\get;

class ConsoleArguments
{
	public static function count()
	{
		global $argc;

		if (isset($argc))
		{
			return $argc;
		}

		if (isset($_SERVER['argc']))
		{
			return $_SERVER['argc'];
		}

		return 0;
	}

	public static function isExist($argument, $value = null, $value_strict = false)
	{
		$arguments = static::get();

		if (!isset($arguments[$argument]))
		{
			return false;
		}

		if ($value === null)
		{
			return true;
		}

			 if ($value_strict) { return $arguments[$argument] === $value; }
		else                    { return $arguments[$argument]  == $value; }
	}

	public static function get($name = null)
	{
		static $arguments;

		if ($arguments !== null)
		{
			if ($name !== null)
			{
				return get($arguments, $name);
			}

			return $arguments;
		}


		if (!APP_CLI_ARGUMENTS)
		{
			return []; # do not call `exception` here to prevent endless loop
		}


		$arguments = static::getRaw();

		if (!$arguments)
		{
			return [];
		}


		array_shift($arguments); # skip php script name

		if (!$arguments)
		{
			return [];
		}


		$arguments_amount = count($arguments);

		for ($i = 0; $i < $arguments_amount; $i++)
		{
			$argument       = $arguments[$i];
			$argument_clean = static::clean($argument);

			if (isset($arguments[$i + 1]) and Strings::startsWith($argument, '-'))
			{
				$argument_next = $arguments[$i + 1];

				if (!Strings::startsWith($argument_next, '-'))
				{
					$arguments[$argument_clean] = static::clean($argument_next);

					$i++;

					continue;
				}
			}

			$arguments[$argument_clean] = $argument_clean;
		}

		if ($name !== null)
		{
			return get($arguments, $name);
		}

		return $arguments;
	}

	public static function getRaw()
	{
		global $argv;

		static $arguments_raw;

		if ($arguments_raw !== null)
		{
			return $arguments_raw;
		}

		if (isset($_SERVER['argv']))
		{
			$arguments_raw = $_SERVER['argv'];
		}

		if (isset($argv))
		{
			$arguments_raw = $argv;
		}

		return $arguments_raw;
	}

	private static function clean($string)
	{
		return Strings::trimLeft($string, '-');
	}
}
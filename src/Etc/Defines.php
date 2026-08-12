<?php

namespace Stackstra\Etc;

use Stackstra\Types\Strings;

class Defines
{
	public static function get(): array
	{
		return get_defined_constants();
	}

	public static function startsWith($string, $case_sensitive = true): array
	{
		$result = [];

		foreach (static::get() as $key => $val)
		{
			if (Strings::startsWith($key, $string, $case_sensitive))
			{
				$result[$key] = $val;
			}
		}

		return $result;
	}

	public static function endsWith($string, $case_sensitive = true): array
	{
		$result = [];

		foreach (static::get() as $key => $val)
		{
			if (Strings::endsWith($key, $string, $case_sensitive))
			{
				$result[$key] = $val;
			}
		}

		return $result;
	}

	public static function set($key, $val)
	{
		if (!defined($key))
		{
			define($key, $val);
		}
	}
}
<?php

namespace Stackstra\INI;

use Stackstra\Filesystem\File;
use Stackstra\Types\Chars;
use Stackstra\Types\Strings;

use function Stackstra\get;

class INI
{
	private $settings = [];

	public function parseFile($path_ini)
	{
		$data = File::read($path_ini);

		static::parseString($data);
	}

	public function parseString($data)
	{
		$this->settings = [];


		$lines = Strings::lines($data);

		foreach ($lines as $line)
		{
			$line = trim($line);

			$char = Chars::first($line);

			if ($char == '[' or $char == ';')
			{
				continue;
			}

			if (!Strings::contains($line, '='))
			{
				continue;
			}


			$key = Strings::readUntil($line, '=');
			$val = Strings::readAfter($line, '=');

			$key = Strings::trim($key);
			$val = Strings::trim($val);

			$this->settings[$key] = $val;
		}
	}

	public function get($index = null)
	{
		if ($index !== null)
		{
			return get($this->settings, $index);
		}

		return $this->settings;
	}
}

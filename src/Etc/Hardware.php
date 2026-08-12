<?php

namespace Stackstra\Etc;

class Hardware
{
	public static function cores()
	{
		static $cores;

		if ($cores === null)
		{
			if (OS::IS_LINUX)
			{
				if (is_readable('/proc/cpuinfo'))
				{
					$cores = substr_count(file_get_contents('/proc/cpuinfo'), 'processor');
				}
			}
			else if (OS::IS_WINDOWS)
			{
				$cores = getenv('NUMBER_OF_PROCESSORS');
			}

			$cores = max(1, (int) $cores);
		}

		return $cores;
	}
}
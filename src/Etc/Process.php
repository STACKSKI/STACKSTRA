<?php

namespace Stackstra\Etc;

class Process
{
	public static function leader()
	{
		return posix_setsid();
	}

	public static function isExist($pid): bool
	{
		return posix_kill($pid, 0);
	}
}
<?php

namespace Stackstra\Etc;

class Buffering
{
	public static function start()
	{
		return ob_start();
	}

	public static function level()
	{
		return ob_get_level();
	}

	public static function getClean()
	{
		return ob_get_clean();
	}

	public static function endClean()
	{
		return ob_end_clean();
	}
}
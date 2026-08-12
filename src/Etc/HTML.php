<?php

namespace Stackstra\Etc;

class HTML
{
	public static function escape($string)
	{
		return htmlentities((string) $string);
	}
}
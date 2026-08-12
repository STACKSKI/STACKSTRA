<?php

namespace Stackstra\Etc;

class Nullptr
{
	public static function instance()
	{
		static $instance = new self;

		return $instance;
	}
}

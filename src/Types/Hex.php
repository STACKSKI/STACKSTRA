<?php

namespace Stackstra\Types;

use Stackstra\Etc\ASCII;

class Hex
{
	# TODO: "float" as a return type an error?
	public static function toInt($hex): int|float
	{
		return hexdec($hex);
	}

	public static function toBin($hex): string|false
	{
		return hex2bin($hex);
	}

	public static function isPrintable($hex): bool
	{
		$int = static::toInt($hex);

		return ASCII::isPrintableIndex($int);
	}
}
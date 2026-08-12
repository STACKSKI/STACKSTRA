<?php

namespace Stackstra\Types;

class GUID
{
	public static function toBin($string): string|false
	{
		return hex2bin(static::toHex($string));
	}

	public static function toHex($string): string
	{
		return str_replace('-', '', $string);
	}
}
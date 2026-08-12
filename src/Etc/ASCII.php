<?php

namespace Stackstra\Etc;

use Stackstra\Types\Chars;
use Stackstra\Types\Hex;
use Stackstra\Types\Integer;

class ASCII
{
	public static function isPrintableIndex($index)
	{
		return $index >= 32 && $index <= 126;
	}

	public static function isPrintableChar($char)
	{
		$index = static::charToIndex($char);

		return static::isPrintableIndex($index);
	}

	public static function isPrintableHex($hex)
	{
		$index = static::hexToIndex($hex);

		return static::isPrintableIndex($index);
	}

	public static function indexToChar($number)
	{
		return chr($number);
	}

	public static function indexToHex($number)
	{
		return Integer::toHex($number);
	}

	public static function charToIndex($char)
	{
		return ord($char);
	}

	public static function charToHex($char)
	{
		return Chars::toHex($char);
	}

	public static function hexToIndex($hex)
	{
		return Hex::toInt($hex);
	}

	public static function hexToChar($hex)
	{
		return Hex::toBin($hex);
	}
}
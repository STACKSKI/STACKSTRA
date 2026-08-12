<?php

namespace Stackstra\CSS;

use Stackstra\Types\Strings;

class Compressor
{
	public static function compress($data)
	{
		$data = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $data);

		$data = Strings::replace($data, ["\r\n", "\r", "\n", "\t", '  '], '');
		$data = Strings::replace($data, '{ ', '{');
		$data = Strings::replace($data, ' }', '}');
		$data = Strings::replace($data, '; ', ';');
		$data = Strings::replace($data, ', ', ',');
		$data = Strings::replace($data, ' {', '{');
		$data = Strings::replace($data, '} ', '}');
		$data = Strings::replace($data, ': ', ':');
		$data = Strings::replace($data, ' ,', ',');
		$data = Strings::replace($data, ' ;', ';');

		return $data;
	}
}
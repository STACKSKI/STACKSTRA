<?php

namespace Stackstra\Lang;

# Author - https:#gist.github.com/tbrianjones
# Source - https:#gist.github.com/tbrianjones/ba0460cc1d55f357e00b
#
# The MIT License (MIT)
#
# Copyright (c) 2015
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
# THE SOFTWARE.
#
# Thanks to http:#www.eval.ca/articles/php-pluralize (MIT license)
#           http:#dev.rubyonrails.org/browser/trunk/activesupport/lib/active_support/inflections.rb (MIT license)
#           http:#www.fortunecity.com/bally/durrus/153/gramch13.html
#           http:#www2.gsu.edu/~wwwesl/egw/crump.htm
#
# Changes (12/17/07)
#   Major changes
#   --
#   Fixed irregular noun algorithm to use regular expressions just like the original Ruby source.
#       (this allows for things like fireman -> firemen
#   Fixed the order of the singular array, which was backwards.
#
#   Minor changes
#   --
#   Removed incorrect pluralization rule for /([^aeiouy]|qu)ies$/ => $1y
#   Expanded on the list of exceptions for *o -> *oes, and removed rule for buffalo -> buffaloes
#   Removed dangerous singularization rule for /([^f])ves$/ => $1fe
#   Added more specific rules for singularizing lives, wives, knives, sheaves, loaves, and leaves and thieves
#   Added exception to /(us)es$/ => $1 rule for houses => house and blouses => blouse
#   Added excpetions for feet, geese and teeth
#   Added rule for deer -> deer
#
# Changes:
#   Removed rule for virus -> viri
#   Added rule for potato -> potatoes
#   Added rule for *us -> *uses

use Stackstra\Types\Strings;

class English
{
	const DIGITS   = '0123456789';
	const LETTERS  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const ALPHABET = self::DIGITS . self::LETTERS;

	private static array $plural = array
	(
		'/(quiz)$/i'                     => '$1zes',
		'/^(ox)$/i'                      => '$1en',
		'/([m|l])ouse$/i'                => '$1ice',
		'/(matr|vert|ind)ix|ex$/i'       => '$1ices',
		'/(x|ch|ss|sh)$/i'               => '$1es',
		'/([^aeiouy]|qu)y$/i'            => '$1ies',
		'/(hive)$/i'                     => '$1s',
		'/(?:([^f])fe|([lr])f)$/i'       => '$1$2ves',
		'/(shea|lea|loa|thie)f$/i'       => '$1ves',
		'/sis$/i'                        => 'ses',
		'/([ti])um$/i'                   => '$1a',
		'/(tomat|potat|ech|her|vet)o$/i' => '$1oes',
		'/(bu)s$/i'                      => '$1ses',
		'/(alias)$/i'                    => '$1es',
		'/(octop)us$/i'                  => '$1i',
		'/(ax|test)is$/i'                => '$1es',
		'/(us)$/i'                       => '$1es',
		'/s$/i'                          => 's',
		'/$/'                            => 's'
	);

	private static array $singular = array
	(
		'/(quiz)zes$/i'              => '$1',
		'/(matr)ices$/i'             => '$1ix',
		'/(vert|ind)ices$/i'         => '$1ex',
		'/^(ox)en$/i'                => '$1',
		'/(alias)es$/i'              => '$1',
		'/(octop|vir)i$/i'           => '$1us',
		'/(cris|ax|test)es$/i'       => '$1is',
		'/(shoe)s$/i'                => '$1',
		'/(o)es$/i'                  => '$1',
		'/(bus)es$/i'                => '$1',
		'/([m|l])ice$/i'             => '$1ouse',
		'/(x|ch|ss|sh)es$/i'         => '$1',
		'/(m)ovies$/i'               => '$1ovie',
		'/(s)eries$/i'               => '$1eries',
		'/([^aeiouy]|qu)ies$/i'      => '$1y',
		'/([lr])ves$/i'              => '$1f',
		'/(tive)s$/i'                => '$1',
		'/(hive)s$/i'                => '$1',
		'/(li|wi|kni)ves$/i'         => '$1fe',
		'/(shea|loa|lea|thie)ves$/i' => '$1f',
		'/(^analy)ses$/i'            => '$1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '$1$2sis',
		'/([ti])a$/i'                => '$1um',
		'/(n)ews$/i'                 => '$1ews',
		'/(h|bl)ouses$/i'            => '$1ouse',
		'/(corpse)s$/i'              => '$1',
		'/(us)es$/i'                 => '$1',
		'/s$/i'                      => ''
	);

	private static array $irregular = array
	(
		'move'   => 'moves',
		'foot'   => 'feet',
		'goose'  => 'geese',
		'sex'    => 'sexes',
		'child'  => 'children',
		'man'    => 'men',
		'tooth'  => 'teeth',
		'person' => 'people'
	);

	private static array $uncountable = array
	(
		'sheep',
		'fish',
		'deer',
		'series',
		'species',
		'money',
		'rice',
		'information',
		'equipment'
	);

	public static function pluralize($string): ?string
	{
		# save some time in the case that singular and plural are the same
		if (in_array(Strings::toLowercase($string), self::$uncountable))
		{
			return $string;
		}


		# check for irregular singular forms
		foreach (self::$irregular as $pattern => $result)
		{
			$pattern = '/' . $pattern . '$/i';

			if (preg_match($pattern, $string))
			{
				return preg_replace($pattern, $result, $string);
			}
		}

		# check for matches using regular expressions
		foreach (self::$plural as $pattern => $result)
		{
			if (preg_match($pattern, $string))
			{
				return preg_replace($pattern, $result, $string);
			}
		}

		return $string;
	}

	public static function singularize(string $string): ?string
	{
		# save some time in the case that singular and plural are the same
		if (in_array(Strings::toLowercase($string), self::$uncountable))
		{
			return $string;
		}

		# check for irregular plural forms
		foreach (self::$irregular as $result => $pattern)
		{
			$pattern = '/' . $pattern . '$/i';

			if (preg_match($pattern, $string))
			{
				return preg_replace($pattern, $result, $string);
			}
		}

		# check for matches using regular expressions
		foreach (self::$singular as $pattern => $result)
		{
			if (preg_match($pattern, $string))
			{
				return preg_replace($pattern, $result, $string);
			}
		}

		return $string;
	}

	public static function number($number, $hyphen = '-', $conjunction = ' and ', $separator = ', ', $negative = 'negative ', $decimal = ' point '): string
	{
		if (!is_numeric($number)) { return ''; }

		if ($number < 0)
		{
			return $negative . static::number(abs($number));
		}

		$dictionary =
		[
			0                   => 'zero',
			1                   => 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
			10                  => 'ten',
			11                  => 'eleven',
			12                  => 'twelve',
			13                  => 'thirteen',
			14                  => 'fourteen',
			15                  => 'fifteen',
			16                  => 'sixteen',
			17                  => 'seventeen',
			18                  => 'eighteen',
			19                  => 'nineteen',
			20                  => 'twenty',
			30                  => 'thirty',
			40                  => 'fourty',
			50                  => 'fifty',
			60                  => 'sixty',
			70                  => 'seventy',
			80                  => 'eighty',
			90                  => 'ninety',
			100                 => 'hundred',
			1000                => 'thousand',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		];


		$fraction = null;

		if (Strings::contains($number, '.'))
		{
			[$number, $fraction] = explode('.', $number);
		}


		if ($number < 21)
		{
			$string = $dictionary[$number];
		}
		else if ($number < 100)
		{
			$tens = ((int) ($number / 10)) * 10;
			$units = $number % 10;

			$string = $dictionary[$tens];

			if ($units)
			{
				$string .= $hyphen . $dictionary[$units];
			}
		}
		else if($number < 1000)
		{
			$hundreds = $number / 100;
			$remainder = $number % 100;

			$string = $dictionary[(int) $hundreds] . ' ' . $dictionary[100];

			if ($remainder)
			{
				$string .= $conjunction . static::number($remainder);
			}
		}
		else
		{
			$base_unit = pow(1000, floor(log($number, 1000)));
			$base_units = (int) ($number / $base_unit);
			$remainder = $number % $base_unit;

			$string = static::number($base_units) . ' ' . $dictionary[$base_unit];

			if ($remainder)
			{
				$string .= $remainder < 100 ? $conjunction : $separator;
				$string .= static::number($remainder);
			}
		}

		if ($fraction !== null && is_numeric($fraction))
		{
			$string .= $decimal;

			$words = [];

			foreach (Strings::chars($fraction) as $number)
			{
				$words[] = $dictionary[$number];
			}

			$string .= implode(' ', $words);
		}

		return $string;
	}
}
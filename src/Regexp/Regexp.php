<?php

namespace Stackstra\Regexp;

use function Stackstra\get;

class Regexp
{
	const ALPHA       = 'a-z';
	const ALPHA_UPPER = 'A-Z';
	const NUMERIC     = '0-9';
	const UNDERSCORE  = '_';
	const DOT         = '\.';
	const DASH        = '\-';
	const BACKSLASH   = '\\\\';
	const BRACKETS    = '\[\]';
	const AT          = '@';
	const STAR        = '\*';

	const ALPHA_NUM            = self::ALPHA     . self::NUMERIC;
	const ALPHA_NUM_UNDERSCORE = self::ALPHA_NUM . self::UNDERSCORE;

	const ESCAPABLE = '.\+*?[^]$(){}=!<>|:-#';

	public static function keep(?string $string, $pattern, $replace = null): ?string
	{
		if ($string === null)
		{
			return null;
		}

		if ($replace === null)
		{
			$replace = '';
		}

		return preg_replace("/[^$pattern]/i", $replace, $string);
	}

	public static function keepNum                             (?string $string, $replace = null): ?string { return self::keep($string, self::NUMERIC,                                             $replace); }
	public static function keepAlpha                           (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA,                                               $replace); }
	public static function keepAlphaNum                        (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM,                                           $replace); }
	public static function keepAlphaNumBrackets                (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM . self::BRACKETS,                          $replace); }
	public static function keepAlphaNumUnderscore              (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM_UNDERSCORE,                                $replace); }
	public static function keepAlphaNumUnderscoreBrackets      (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM_UNDERSCORE . self::BRACKETS,               $replace); }
	public static function keepAlphaNumUnderscoreAt            (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM_UNDERSCORE . self::AT,                     $replace); }
	public static function keepAlphaNumUnderscoreDash          (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM_UNDERSCORE . self::DASH,                   $replace); }
	public static function keepAlphaNumUnderscoreDashBackslash (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM_UNDERSCORE . self::DASH . self::BACKSLASH, $replace); }
	public static function keepAlphaNumUnderscoreDot           (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM_UNDERSCORE . self::DOT,                    $replace); }
	public static function keepAlphaNumUnderscoreDotDash       (?string $string, $replace = null): ?string { return self::keep($string, self::ALPHA_NUM_UNDERSCORE . self::DOT . self::DASH,       $replace); }

	/**
	 * @param string|null $string
	 *
	 * @return int[]|float[]
	 */
	public static function parseNumbers(?string $string): array
	{
		if ($string === null)
		{
			return [];
		}


		# https://stackoverflow.com/a/6278312

		preg_match_all('!\d+!', $string, $matches);

		return get($matches, 0, []);
	}
}
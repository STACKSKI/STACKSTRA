<?php

namespace Stackstra\Etc;

use Stackstra\Types\Strings;

use function Stackstra\get;

class Visitor
{
	/**
	 * Returns current visitor's IP address
	 *
	 * $ip = visitor::ip(); # string(9) "86.92.16.32"
	 *
	 * @param bool $to_integer
	 *
	 * @return string|int|null
	 */
	public static function ip($to_integer = false): int|string|null
	{
		if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
		{
			# real visitor IP behind CloudFlare network

			$_SERVER['HTTP_CLIENT_IP'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
			$_SERVER['REMOTE_ADDR']    = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}


		$ip = get($_SERVER, 'HTTP_CLIENT_IP');

		if (!$ip) { $ip = get($_SERVER, 'HTTP_X_FORWARDED_FOR'); }
		if (!$ip) { $ip = get($_SERVER, 'REMOTE_ADDR');          }

		if (!Strings::isIP($ip))
		{
			return null;
		}

		if ($to_integer)
		{
			return ip2long($ip);
		}

		return $ip;
	}

	public static function language(): string
	{
		$languages = Headers::acceptLanguages();

		foreach ($languages as $country => $priority)
		{
			return static::format($country);
		}

		return '';
	}

	public static function languages(): array
	{
		$languages = Headers::acceptLanguages();


		$result = [];

		foreach ($languages as $country => $priority)
		{
			$country_formatted = static::format($country);

			$result[$country_formatted] = $country_formatted;
		}

		$result = array_unique($result);


		return $result;
	}

	private static function format($string): string
	{
		if (Strings::contains($string, '-'))
		{
			$string = Strings::readUntil($string, '-');
		}

		return Strings::toLowercase($string);
	}

	public static function isLanguage($lang)
	{
		return static::language() == static::format($lang);
	}


	#####################################################################################
	# http://www.iana.org/assignments/language-subtag-registry/language-subtag-registry #
	#####################################################################################

	public static function isEnglish():    bool { return static::isLanguage('en'); }
	public static function isSpanish():    bool { return static::isLanguage('es'); }
	public static function isChinese():    bool { return static::isLanguage('zh'); }
	public static function isRussian():    bool { return static::isLanguage('ru'); }


	public static function referer()
	{
		return get($_SERVER, 'HTTP_REFERER');
	}

	public static function isReferer($url, $case_sensitive = false): bool
	{
		return Strings::contains(static::referer(), $url, $case_sensitive);
	}

	public static function userAgent() { return $_SERVER['HTTP_USER_AGENT']; }

	# TODO is_android -> is_device_android/is_os_android (?)
	public static function isAndroid():       bool { return Strings::contains(static::userAgent(), 'android',       false); }
	public static function isWindowsPhone():  bool { return Strings::contains(static::userAgent(), 'windows phone', false); }
	public static function isIphone():        bool { return Strings::contains(static::userAgent(), 'iphone',        false); }
	public static function isIpad():          bool { return Strings::contains(static::userAgent(), 'ipad',          false); }
	public static function isIpod():          bool { return Strings::contains(static::userAgent(), 'ipod',          false); }

	public static function isPhone(): bool
	{
		return static::isAndroid()      ||
		       static::isWindowsPhone() ||
		       static::isIphone()       ||
		       static::isIpad()         ||
		       static::isIpod();
	}
}
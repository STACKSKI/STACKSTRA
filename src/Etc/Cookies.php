<?php

namespace Stackstra\Etc;

use function Stackstra\get;

use const Stackstra\SECONDS_IN_MONTH;
use const Stackstra\SECONDS_IN_YEAR;

class Cookies
{
	public static function get($name = null)
	{
		if ($name === null)
		{
			return $_COOKIE;
		}

		return get($_COOKIE, $name);
	}

	public static function inc($name, $n = 1)
	{
		return static::set($name, static::get($name) + $n);
	}

	public static function dec($name, $n = 1)
	{
		return static::set($name, static::get($name) - $n);
	}

	public static function set($name, $value = 1, $expire = SECONDS_IN_MONTH, $path = '/', $domain = null, $secure = null, $httponly = null)
	{
		$_COOKIE[$name] = $value;

		return setcookie($name, $value, time() + $expire, $path, $domain, $secure, $httponly);
	}

	public static function delete($name = null, $path = '/')
	{
		if ($name === null)
		{
			foreach ($_COOKIE as $key => $val)
			{
				static::delete($key);
			}

			return true;
		}


		unset($_COOKIE[$name]);

		return setcookie($name, '', time() - SECONDS_IN_YEAR, $path);
	}

	public static function isExist($name, $value = false)
	{
		if ($value === false)
		{
			return isset($_COOKIE[$name]);
		}

		return static::get($name) == $value;
	}
}

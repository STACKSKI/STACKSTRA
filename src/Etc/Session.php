<?php

namespace Stackstra\Etc;

use function Stackstra\get;

class Session
{
	/**
	 * Session initialization
	 *
	 * @return bool
	 */
	public static function start(): bool
	{
		if (APP_CLI)
		{
			return false;
		}

		switch (session_status())
		{
			case PHP_SESSION_DISABLED:
			case PHP_SESSION_ACTIVE:
				return false;
		}

		return session_start();
	}

	public static function close()
	{
		session_write_close();
	}

	public static function id(): string
	{
		return session_id();
	}

	/**
	 * @param mixed $key     key to fetch, or null to get the whole session array
	 * @param mixed $default returned if $key isn't set
	 *
	 * @return mixed
	 */
	public static function get($key = null, $default = null): mixed
	{
		if ($key === null)
		{
			return $_SESSION;
		}

		return get($_SESSION, $key, $default);
	}

	public static function set($key, $value): bool
	{
		$_SESSION[$key] = $value;

		return true;
	}

	public static function remove($key)
	{
		if (static::isExist($key))
		{
			unset($_SESSION[$key]);
		}
	}

	public static function isExist($key): bool
	{
		if (!isset($_SESSION))
		{
			return false;
		}

		return isset($_SESSION[$key]);
	}

	public static function isEqual($key, $value, $strict = true): bool
	{
		if (!static::isExist($key))
		{
			return false;
		}

		if ($strict)
		{
			return $value === static::get($key);
		}

		return $value == static::get($key);
	}

	public static function status(): int
	{
		return session_status();
	}

	public static function isActive(): bool
	{
		return session_status() === PHP_SESSION_ACTIVE;
	}

	/**
	 * @en Session destruction
	 *
	 * @return bool
	 */
	public static function destroy(): bool
	{
		if (!static::isActive())
		{
			return false;
		}


		$_SESSION = [];

		if (ini_get("session.use_cookies"))
		{
			$params = session_get_cookie_params();

			setcookie
			(
				session_name(),
				'',
				time() - 42000,
				$params["path"],
				$params["domain"],
				$params["secure"],
				$params["httponly"]
			);
		}

		return session_destroy();
	}

	public static function erase(): bool
	{
		if (!static::isActive())
		{
			return false;
		}

		return session_unset();
	}
}

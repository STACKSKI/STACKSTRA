<?php

namespace Stackstra\Etc;

class Debug
{
	protected static ?bool $is_enabled = null;

	public static function enable(): void
	{
		static::$is_enabled = true;
	}

	public static function disable(): void
	{
		static::$is_enabled = false;
	}

	public static function isEnabled(): bool
	{
		if (static::$is_enabled === null)
		{
			static::$is_enabled = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
		}

		return static::$is_enabled;
	}
}

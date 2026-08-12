<?php

namespace Stackstra\Exceptions;

use Exception;

class Exceptions
{
	const string E_ERROR      = 'ERROR';
	const string E_WARNING    = 'WARNING';
	const string E_NOTICE     = 'NOTICE';
	const string E_DEPRECATED = 'DEPRECATED';
	const string E_STRICT     = 'STRICT';
	const string E_UNKNOWN    = 'UNKNOWN';
	const string E_SUCCESS    = 'OK';
	const string E_VALIDATION = 'VALIDATION';

	const array LIST_E_ERRORS      = [self::E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];
	const array LIST_E_WARNINGS    = [self::E_WARNING, E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING];
	const array LIST_E_NOTICES     = [self::E_NOTICE, E_NOTICE, E_USER_NOTICE];
	const array LIST_E_DEPRECATES  = [self::E_DEPRECATED, E_DEPRECATED, E_USER_DEPRECATED];
	const array LIST_E_STRICTS     = [self::E_STRICT, E_STRICT];
	const array LIST_E_VALIDATIONS = [self::E_VALIDATION];
	const array LIST_E_SUCCESS     = [self::E_SUCCESS];

	public static function error     ($message): bool { return static::trigger($message, self::E_ERROR);      }
	public static function warning   ($message): bool { return static::trigger($message, self::E_WARNING);    }
	public static function notice    ($message): bool { return static::trigger($message, self::E_NOTICE);     }
	public static function deprecated($message): bool { return static::trigger($message, self::E_DEPRECATED); }
	public static function strict    ($message): bool { return static::trigger($message, self::E_STRICT);     }
	public static function unknown   ($message): bool { return static::trigger($message, self::E_UNKNOWN);    }

	public static function trigger($message, $error_type): bool
	{
		error_log("[$error_type] $message");

		if ($error_type === self::E_ERROR)
		{
			throw new Exception($message);
		}

		$level = match ($error_type)
		{
			self::E_WARNING    => E_USER_WARNING,
			self::E_NOTICE     => E_USER_NOTICE,
			self::E_DEPRECATED => E_USER_DEPRECATED,
			self::E_STRICT     => E_USER_NOTICE,

			default => E_USER_NOTICE,
		};

		return trigger_error($message, $level);
	}
}

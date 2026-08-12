<?php

namespace Stackstra\Types;

use function Stackstra\get;

class Objects
{
	/**
	 * Recursively casts an array/object tree to a plain array, keeping scalar leaf values as-is
	 */
	public static function toArray($object): ?array
	{
		if (is_array($object) or is_object($object))
		{
			$result = null;

			foreach ($object as $key => $value)
			{
				     if (is_array($value) or is_object($value)) { $result[$key] = static::toArray($value); }
				else                                            { $result[$key] = $value;                  }
			}

			return $result;
		}

		return null;
	}

	public static function get($object, $property, $default = null)
	{
		return get($object, $property, $default);
	}

	public static function properties($object): array
	{
		return get_object_vars($object);
	}

	public static function hasProperty($object, string|int $property): bool
	{
		return isset($object->{$property});
	}

	public static function isEmpty($object): bool
	{
		return empty((array) $object);
	}
}
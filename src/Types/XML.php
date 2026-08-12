<?php

namespace Stackstra\Types;

use function Stackstra\false_to_null;

class XML
{
	/**
	 * @return string|null null if $xml couldn't be parsed
	 */
	public static function toJSON(string $xml): ?string
	{
		$simplexml = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		if ($simplexml === false)
		{
			return null;
		}

		$result = json_encode($simplexml);

		return false_to_null($result);
	}

	/**
	 * Parses $xml via toJSON(), then decodes that JSON into a stdClass tree
	 */
	public static function toObject(string $xml): mixed
	{
		$json = static::toJSON($xml);

		if ($json === null)
		{
			return null;
		}

		return json_decode($json);
	}

	/**
	 * Parses $xml via toObject(), then recursively casts it to an array
	 */
	public static function toArray(string $xml): ?array
	{
		$objects = static::toObject($xml);

		return Objects::toArray($objects);
	}
}
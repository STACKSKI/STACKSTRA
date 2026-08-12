<?php

namespace Stackstra\Types;

use function Stackstra\get;

class Resource
{
	/**
	 * @param resource    $resource
	 * @param string|null $type single meta key to return, or the full stream_get_meta_data() array
	 *
	 * @return mixed
	 */
	public static function info($resource, ?string $type = null): mixed
	{
		$info = stream_get_meta_data($resource);

		if ($type === null)
		{
			return $info;
		}

		return get($info, $type);
	}

	public static function path($resource): ?string
	{
		return static::info($resource, 'uri');
	}
}
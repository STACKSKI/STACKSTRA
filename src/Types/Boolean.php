<?php

namespace Stackstra\Types;

/**
 * @category types
 */
class Boolean
{
	/**
	 * Returns random bool value
	 *
	 * $rand = bool::rand();
	 *
	 * @return bool
	 */
	public static function rand()
	{
		return mt_rand(0, 1) === 0;
	}

	public static function toString($bool)
	{
		if ($bool)
		{
			return 'true';
		}

		return 'false';
	}
}
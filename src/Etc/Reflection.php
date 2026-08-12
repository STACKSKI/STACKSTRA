<?php

namespace Stackstra\Etc;

use Stackstra\Types\Items;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;

class Reflection
{
	public static function instance($class): ReflectionClass
	{
		return new ReflectionClass($class);
	}

	public static function getConstants($class, ?int $filter = null)
	{
		return self::instance($class)->getConstants($filter);
	}

	public static function getConstantsPublic($class)
	{
		return self::getConstants($class, ReflectionClassConstant::IS_PUBLIC);
	}

	public static function getConstantsPublicStartWith($class, string $string): array
	{
		$constants = self::getConstantsPublic($class);

		return Items::keepKeysStartWith($constants, $string);
	}

	/**
	 * @param          $class
	 * @param int|null $filter
	 *
	 * @return ReflectionMethod[]
	 */
	public static function getMethods($class, ?int $filter = null): array
	{
		return self::instance($class)->getMethods($filter);
	}

	public static function getClassShortName($class): string
	{
		return self::instance($class)->getShortName();
	}
}

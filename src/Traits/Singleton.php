<?php

namespace Stackstra\Traits;

trait Singleton
{
	private static ?self $instance = null;

	public static function instance(mixed ...$arguments): static
	{
		return self::$instance ??= new static(...$arguments);
	}

	public static function make(mixed ...$arguments): static
	{
		return new static(...$arguments);
	}
}

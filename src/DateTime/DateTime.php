<?php

namespace Stackstra\DateTime;

class DateTime
{
	const string FORMAT_YMD     = 'Ymd';
	const string FORMAT_YMD_HIS = 'YmdHis';

	protected int $timestamp;

	public function __construct(?int $timestamp = null)
	{
		$this->timestamp = $timestamp ?? time();
	}

	public static function make(?int $timestamp = null): self
	{
		return new self($timestamp);
	}

	public function format(string $format): string
	{
		return date($format, $this->timestamp);
	}

	public function formatYmd(): string
	{
		return $this->format(self::FORMAT_YMD);
	}

	public function formatYmdHis(): string
	{
		return $this->format(self::FORMAT_YMD_HIS);
	}
}

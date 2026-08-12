<?php

namespace Stackstra\Etc;

use Stackstra\Console\Console;

class PHP
{
	public static function gid() { return getmygid(); }
	public static function pid() { return getmypid(); }
	public static function uid() { return getmyuid(); }

	public static function memoryUsage    ($real_usage = true): int { return memory_get_usage     ($real_usage); }
	public static function memoryUsagePeak($real_usage = true): int { return memory_get_peak_usage($real_usage); }

	public static function memoryUsageMegabytes($real_usage = true)
	{
		$usage = static::memoryUsage($real_usage);

		return Convert::bytesToMegabytes($usage, 2);
	}

	public static function memoryUsagePeakMegabytes($real_usage = true)
	{
		$usage = static::memoryUsagePeak($real_usage);

		return Convert::bytesToMegabytes($usage, 2);
	}

	public static function memoryUsageReport()
	{
		$format = fn($number) => number_format($number, decimals: 2, thousands_separator: '');

		$memory_used           = $format(self::memoryUsageMegabytes(real_usage: false));
		$memory_allocated      = $format(self::memoryUsageMegabytes(real_usage: true));

		$memory_used_peak      = $format(self::memoryUsagePeakMegabytes(real_usage: false));
		$memory_allocated_peak = $format(self::memoryUsagePeakMegabytes(real_usage: true));

		Console::lines();
		Console::write('[memory]:');
		Console::write("allocated: {$memory_allocated}MB ({$memory_allocated_peak}MB peak)");
		Console::write("used:      {$memory_used}MB ({$memory_used_peak}MB peak)");
	}

	public static function user()
	{
		return get_current_user();
	}

	public static function version(): string
	{
		return phpversion();
	}

	public static function versionCompare($version_a, $version_b, $operator)
	{
		return version_compare($version_a, $version_b, $operator);
	}

	public static function is32(): bool { return PHP_INT_SIZE === 4; }
	public static function is64(): bool { return PHP_INT_SIZE === 8; }

	public static function minInt(): int { return PHP_INT_MIN; }
	public static function maxInt(): int { return PHP_INT_MAX; }
}

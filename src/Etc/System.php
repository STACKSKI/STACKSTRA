<?php

namespace Stackstra\Etc;

use Stackstra\Types\Strings;

class System
{
	/**
	 * https://stackoverflow.com/a/12430799
	 *
	 * x86/x64 is always little-endian, but PHP could technically run on big-endian machines ... somewhere
	 *
	 *
	 * Byte order system checker, returns `true` on little endian systems and `false` on big endian systems
	 *
	 * $result = sysinfo::is_little_endian();
	 *
	 * @return bool
	 */
	public static function isLittleEndian(): bool
	{
		$int = 0x00FF;

		return $int === current(unpack('v', pack('S', $int)));
	}

	public static function isBigEndian(): bool
	{
		return !System::isLittleEndian();
	}

	public static function uname($mode = null): string
	{
		return php_uname($mode);
	}

	public static function hostname():  string { return static::uname('n'); }
	public static function processor(): string { return static::uname('m'); }
	public static function os():        string { return static::uname('s'); }


	# OS definition: https://en.wikipedia.org/wiki/Uname#Table_of_standard_uname_output

	public static function isOsLinux()  : bool { return Strings::startsWith(PHP_OS, ['linux', 'gnu'],  false); }
	public static function isOsWindows(): bool { return Strings::startsWith(PHP_OS, ['win', 'cygwin'], false); }
	public static function isOsMac()    : bool { return Strings::startsWith(PHP_OS, 'darwin',          false); }
	public static function isOsFreebsd(): bool { return Strings::startsWith(PHP_OS, 'freebsd',         false); }
}
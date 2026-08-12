<?php

namespace Stackstra;

# https://stackoverflow.com/a/43243743/1597430

use Closure;
use Stackstra\Cache\Cache;
use Stackstra\Console\Shell;
use Stackstra\Etc\Debug;
use Stackstra\Etc\Nullptr;
use Stackstra\Etc\System;
use Stackstra\Etc\Timer;

const SECOND = 1;
const BYTE   = 1;

const SECONDS_IN_NANOSECOND  = 0.000000001;   # 1 nanosecond  = 10^-9 seconds
const SECONDS_IN_MICROSECOND = 0.000001;      # 1 microsecond = 10^-6 seconds
const SECONDS_IN_MILLISECOND = 0.001;         # 1 millisecond = 10^-3 seconds
const SECONDS_IN_MINUTE      = 60;            # 1 minute      = 60 seconds
const SECONDS_IN_HOUR        = 3600;          # 1 hour        = 60*60 seconds
const SECONDS_IN_DAY         = 86400;         # 1 day         = 60*60*24 seconds
const SECONDS_IN_WEEK        = 604800;        # 1 week        = 60*60*24*7 seconds
const SECONDS_IN_MONTH       = 2592000;       # 1 month       = 60*60*24*30 seconds
const SECONDS_IN_MONTH_28    = 2419200;       # 1 month       = 60*60*24*28 seconds
const SECONDS_IN_MONTH_29    = 2505600;       # 1 month       = 60*60*24*29 seconds
const SECONDS_IN_MONTH_30    = 2592000;       # 1 month       = 60*60*24*30 seconds
const SECONDS_IN_MONTH_31    = 2678400;       # 1 month       = 60*60*24*31 seconds
const SECONDS_IN_YEAR        = 31536000;      # 1 year        = 60*60*24*365 seconds
const SECONDS_IN_YEAR_LEAP   = 31622400;      # 1 year        = 60*60*24*366 seconds

const MICROSECONDS_IN_MILLISECOND = 1000;

const MILLISECONDS_IN_MICROSECOND = 0.001;

const NANOSECONDS_IN_SECOND = 1000000000;


const BYTES_IN_KILOBYTE      = 1024;          # 1 kilobyte    = 1024 bytes
const BYTES_IN_MEGABYTE      = 1048576;       # 1 megabyte    = 1048576 bytes
const BYTES_IN_GIGABYTE      = 1073741824;    # 1 gigabyte    = 1073741824 bytes
const BYTES_IN_TERABYTE      = 1099511627776; # 1 terabyte    = 1099511627776 bytes

const MASK_0  = 0;
const MASK_1  = 1;
const MASK_2  = 2;
const MASK_3  = 4;
const MASK_4  = 8;
const MASK_5  = 16;
const MASK_6  = 32;
const MASK_7  = 64;
const MASK_8  = 128;
const MASK_9  = 256;
const MASK_10 = 512;
const MASK_11 = 1024;
const MASK_12 = 2048;
const MASK_13 = 4096;
const MASK_14 = 8192;
const MASK_15 = 16384;
const MASK_16 = 32768;
const MASK_17 = 65536;
const MASK_18 = 131072;
const MASK_19 = 262144;
const MASK_20 = 524288;
const MASK_21 = 1048576;
const MASK_22 = 2097152;
const MASK_23 = 4194304;
const MASK_24 = 8388608;
const MASK_25 = 16777216;
const MASK_26 = 33554432;
const MASK_27 = 67108864;
const MASK_28 = 134217728;
const MASK_29 = 268435456;
const MASK_30 = 536870912;
const MASK_31 = 1073741824;
const MASK_32 = 2147483648;
const MASK_33 = 4294967296;
const MASK_34 = 8589934592;
const MASK_35 = 17179869184;
const MASK_36 = 34359738368;
const MASK_37 = 68719476736;
const MASK_38 = 137438953472;
const MASK_39 = 274877906944;
const MASK_40 = 549755813888;
const MASK_41 = 1099511627776;
const MASK_42 = 2199023255552;
const MASK_43 = 4398046511104;
const MASK_44 = 8796093022208;
const MASK_45 = 17592186044416;
const MASK_46 = 35184372088832;
const MASK_47 = 70368744177664;
const MASK_48 = 140737488355328;
const MASK_49 = 281474976710656;
const MASK_50 = 562949953421312;
const MASK_51 = 1125899906842624;
const MASK_52 = 2251799813685248;
const MASK_53 = 4503599627370496;
const MASK_54 = 9007199254740992;
const MASK_55 = 18014398509481984;
const MASK_56 = 36028797018963968;
const MASK_57 = 72057594037927936;
const MASK_58 = 144115188075855872;
const MASK_59 = 288230376151711744;
const MASK_60 = 576460752303423488;
const MASK_61 = 1152921504606846976;
const MASK_62 = 2305843009213693952;
const MASK_63 = 4611686018427387904;


# https://bugs.php.net/bug.php?id=77913
# -------------------------------------
# multiline syslog support

ini_set('syslog.filter', 'raw');


$namespace = explode('\\', __NAMESPACE__);
$namespace = reset($namespace);

define('APP_NAMESPACE', $namespace);

if (!defined('APP_CLI'))           { define('APP_CLI',           php_sapi_name() == 'cli'); }
if (!defined('APP_CLI_ARGUMENTS')) { define('APP_CLI_ARGUMENTS', ini_get('register_argc_argv')); }

if (!defined('APP_AJAX'))
{
	define('APP_AJAX', !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

if (!defined('APP_CRYPT_HASH_ALGORITHM_DEFAULT'))
{
	define('APP_CRYPT_HASH_ALGORITHM_DEFAULT', 'sha512');
}


if (!defined('APP_STRING_ENCODING'))
{
	define('APP_STRING_ENCODING', 'UTF-8');
}


define('APP_BYTE_ORDER_IS_LITTLE_ENDIAN', System::isLittleEndian());
define('APP_BYTE_ORDER_IS_BIG_ENDIAN',    !APP_BYTE_ORDER_IS_LITTLE_ENDIAN);


define('NULLPTR', Nullptr::instance());


function is_nullptr($variable)
{
	return $variable instanceof Nullptr;
}


/**
 * Get an item from an array. Intended for use with $_REQUEST by default
 *
 * # $_REQUEST parse (default usage)
 *
 * debug($_REQUEST);    # Array
 *                      # (
 *                      #     [user] => Sandy
 *                      #     [host] => localhost
 *                      #     [pass] => XHIIGygSsr
 *                      # )
 *
 * $user = get('user'); # string(5) "Sandy"
 * $host = get('host'); # string(9) "localhost"
 * $pass = get('pass'); # string(10) "XHIIGygSsr"
 *
 *
 * # array parse (advanced usage)
 *
 * $array = array('username' => 'Sandy', 'password' => "XHIIGygSsr");
 *
 * $username = get($array, 'username'); # string(5) "Sandy"
 * $password = get($array, 'password'); # string(10) "XHIIGygSsr"
 *
 *
 * # you can also specify a default value for return if the specified array element not found
 *
 * $undefined = get('undefined', $array);              # NULL
 * $undefined = get('undefined', $array, 1);           # int(1)
 * $undefined = get('undefined', $array, false);       # bool(false)
 * $undefined = get('undefined', $array, "not found"); # string(9) "not found"
 *
 * @param mixed $src
 * @param mixed $what
 * @param mixed $default
 *
 * @return mixed
 */
function get(mixed $src, $what = null, $default = null): mixed
{
	if (func_num_args() == 1)
	{
		$what = $src;
		$src = $_REQUEST;
	}


	if (is_array($what))
	{
		$result = [];


		$is_array = is_array($default) || is_object($default);

		foreach ($what as $key => $val)
		{
			     if ($is_array) { $result[$val] = get($default, $key); }
			else                { $result[$val] = $default;            }
		}

		return $result;
	}


	if (is_object($src))
	{
		# property_exists does not work properly with magic properties and a few other cases, so isset should be used first

		if ($src instanceof \ArrayAccess)
		{
			if (isset($src[$what]) or array_key_exists($what, $src))
			{
				return $src[$what];
			}
		}

		if (isset($src->{$what}) or property_exists($src, $what))
		{
			return $src->{$what};
		}
	}
	else if (isset($src[$what]) or ($src !== null and array_key_exists($what, $src)))
	{
		# https://www.php.net/manual/en/function.array-key-exists.php#107786
		# isset + array_key_exists is faster than array_key_exists

		return $src[$what];
	}

	return $default;
}

function set(&$items, $index, $value = null)
{
	     if (is_object($items)) { $items->{$index} = $value; }
	else                        { $items[$index]   = $value; }
}

function is_set($items, $index)
{
	if (is_object($items))
	{
		return isset($items->{$index});
	}

	return isset($items[$index]);
}

function delete(&$items, $index)
{
	     if (is_object($items)) { unset($items->$index); }
	else                        { unset($items[$index]); }
}


///**
// * @param string|array $what
// * @param mixed        $default
// * @param array|null   $src
// *
// * @return mixed
// */
//function fetch($src, $what, $default = null)
//{
//	if ($src === null)
//	{
//		return $default;
//	}
//
//
//	if (is_array($what))
//	{
//		$result = new \stdClass();
//
//		foreach ($what as $field)
//		{
//			if (is_array($default))
//			{
//
//			}
//
//			$result->{$field} = get($src, $field, $default);
//		}
//
//		return $result;
//	}
//
//	return get($src, $what, $default);
//}

function pull(&$haystack, $needle, $default = null)
{
	$value = get($haystack, $needle, $default);

	     if (is_object($haystack)) { unset($haystack->$needle); }
	else                           { unset($haystack[$needle]); }

	return $value;
}


/**
 * @en Human-readable variable dumper
 *
 * $int = 12345;
 *
 * debug($int);                           # 12345             # print_r alias
 * debug($int, true);                     # int(12345)        # var_dump alias
 * debug($int, false, 'number');          # [number]: 12345   # print_r + title
 *
 *
 * $array = array('AAA', 'BBB', 'CCC');
 *
 * debug($array);                         # Array
 *                                        # (
 *                                        #     [0] => AAA
 *                                        #     [1] => BBB
 *                                        #     [2] => CCC
 *                                        # )
 *
 *
 * $object = new \stdClass();
 * $object->int = $int;
 * $object->array = $array;
 *
 * debug($object);                        # stdClass Object
 *                                        # (
 *                                        #     [int] => 12345
 *                                        #     [array] => Array
 *                                        #     (
 *                                        #         [0] => AAA
 *                                        #         [1] => BBB
 *                                        #         [2] => CCC
 *                                        #     )
 *                                        # )
 *
 * @param mixed       $var
 * @param bool        $var_dump
 * @param string|null $title
 */
function debug(mixed $var, $var_dump = false, $title = null)
{
	if (!Debug::isEnabled())
	{
		return;
	}

	if ((defined('APP_CLI') and APP_CLI) or (defined('APP_AJAX') and APP_AJAX) or (defined('APP_API') and APP_API))
	{
		if ($title !== null)
		{
			echo '[' . $title . ']: ';
		}

		     if ($var_dump) { var_dump($var);              }
		else                { print_r($var); echo PHP_EOL; }
	}
	else
	{
		?><pre style="text-align: left!important;"><?php if ($title !== null) { ?><b>[<?= $title ?>]:</b> <?php } if ($var_dump) { var_dump($var); } else { print_r($var); } ?></pre><?php
	}
}

/**
 * Dump variables and immediately terminate execution (unlike debug(), always dumps regardless of Debug::isEnabled())
 *
 * @param mixed ...$vars
 */
function dd(mixed ...$vars): never
{
	foreach ($vars as $var)
	{
		var_dump($var);
	}

	exit(1);
}

/**
 * @param string   $command
 * @param string[] $options
 *
 * @return Shell
 */
function shell(string $command, array $options = []): Shell
{
	return Shell::run($command, $options);
}


/**
 * @return Timer
 */
function timer(): Timer
{
	return Timer::instance();
}

function format(): string
{
	return sprintf(...func_get_args());
}

/**
 * @param mixed $value
 *
 * @return mixed
 */
function false_to_null(mixed $value): mixed
{
	if ($value === false)
	{
		return null;
	}

	return $value;
}

/**
 * @param mixed $value
 *
 * @return mixed
 */
function value(mixed $value): mixed
{
	if ($value instanceof Closure)
	{
		return $value();
	}

	return $value;
}

function cacher($index = null): Cache
{
	static $cachers;

	if ($cachers === null)
	{
		$cachers = [];
	}

	if (!array_key_exists($index, $cachers))
	{
		$cachers[$index] = new Cache();
	}

	return $cachers[$index];
}

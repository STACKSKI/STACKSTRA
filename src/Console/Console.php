<?php

namespace Stackstra\Console;

use Stackstra\Etc\System;
use Stackstra\Types\Floats;
use Stackstra\Types\Strings;

use function Stackstra\get;

class Console
{
	##################################################
	# http://ascii-table.com/ansi-escape-sequences.php
	# ------------------------------------------------
	# 0     All attributes off
	# 1     Bold on
	# 4     Underscore (on monochrome display adapter only)
	# 5     Blink on
	# 7     Reverse video on
	# 8     Concealed on
	# ------------------
	# Foreground colors:
	#
	# 30    Black
	# 31    Red
	# 32    Green
	# 33    Yellow
	# 34    Blue
	# 35    Magenta
	# 36    Cyan
	# 37    White

	const COLORS_FOREGROUND =
	[
		'underline' => 4,
		'black'     => 30,
		'red'       => 31,
		'green'     => 32,
		'yellow'    => 33,
		'blue'      => 34,
		'magenta'   => 35,
		'cyan'      => 36,
		'white'     => 37
	];


	####################
	# Background colors:
	#
	# 40    Black
	# 41    Red
	# 42    Green
	# 43    Yellow
	# 44    Blue
	# 45    Magenta
	# 46    Cyan
	# 47    White

	const COLORS_BACKGROUND =
	[
		'black'   => 40,
		'red'     => 41,
		'green'   => 42,
		'yellow'  => 43,
		'blue'    => 44,
		'magenta' => 45,
		'cyan'    => 46,
		'white'   => 47
	];

	public static function isExist()
	{
		return APP_CLI;
	}

	public static function read($prompt = null)
	{
		if (System::isOsWindows())
		{
			if ($prompt !== null)
			{
				echo $prompt;
			}

			return stream_get_line(STDIN, 1024, PHP_EOL);
		}

		return readline($prompt);
	}

	public static function write($var = '', $bold = false, $replace = false, $newline = PHP_EOL)
	{
		if ($bold)
		{
			$var = static::bold($var);
		}

		     if ($replace) { echo $var . "\r";     }
		else               { echo $var . $newline; }

		return true;
	}

	public static function writeTimestamp($var = '', $bold = false, $replace = false, $newline = PHP_EOL, $timestamp_format = 'Y-m-d H:i:s')
	{
		$string = '[' . date($timestamp_format) . ']';

		if ($var !== '')
		{
			$string = $string . ' ' . $var;
		}

		return static::write($string, $bold, $replace, $newline);
	}

	public static function bold($string)
	{
		return static::colorize($string, true);
	}

	public static function colorize($string, $bold = false, $foreground_color = null, $background_color = null)
	{
		$values = [];

		if ($bold)             { $values[] = 1; }
		if ($foreground_color) { $values[] = self::COLORS_FOREGROUND[$foreground_color]; }
		if ($background_color) { $values[] = self::COLORS_BACKGROUND[$background_color]; }

		$values = implode(';', $values);

		return "\033[" . $values . "m" . $string . "\033[0m"; # \0333[0m = "all attributes off"
	}

	public static function lines($char = '-')
	{
		static::write(Strings::repeat($char, static::cols()));
	}

	public static function cols($default = 80, $refresh = false)
	{
		# TODO: fix it - detect real terminal width instead of always returning $default

		return $default;
	}

	public static function rows($default = 24, $refresh = false)
	{
		# TODO: fix it - detect real terminal height instead of always returning $default

		return $default;
	}

	public static function isExistCommand($command)
	{
		$shell = new Shell("which $command");

		$shell->exec();

		return $shell->code() === 0;
	}

	//public static function log($string, $error_type)
	//{
	//	$error_color = static::get_error_color($error_type);
	//
	//	$status = static::colorize("[$error_type]", true, $error_color);
	//
	//	return static::write("$status $string");
	//}
	//
	//public static function get_error_color($error_type)
	//{
	//	     if (exceptions::is_error     ($error_type)) { return 'red';    }
	//	else if (exceptions::is_warning   ($error_type)) { return 'red';    }
	//	else if (exceptions::is_notice    ($error_type)) { return 'yellow'; }
	//	else if (exceptions::is_deprecated($error_type)) { return 'yellow'; }
	//	else if (exceptions::is_strict    ($error_type)) { return 'yellow'; }
	//	else if (exceptions::is_success   ($error_type)) { return 'green';  }
	//	else                                             { return 'red';    }
	//}

	public static function clear()
	{
		# ANSI: move cursor home, then clear the screen

		echo "\033[H\033[2J";
	}

	/**
	 *
	 * for ($i = 0, $total = 10; $i <= $total; $i++)
	 * {
	 *     console::progress($i, $total);
	 *
	 *     sleep(1);
	 * }
	 *
	 * @param int         $current
	 * @param int         $total
	 * @param string|null $label_current
	 * @param string|null $label_total
	 * @param string|null $postfix
	 * @param int         $cells
	 * @param string      $cell
	 * @param bool        $newline_on_complete
	 */
	public static function progress(int $current, int $total, ?string $label_current = null, ?string $label_total = null, ?string $postfix = null, int $cells = 10, string $cell = '#', bool $newline_on_complete = true)
	{
		if ($current > $total)
		{
			return;
		}

		$total_length = Strings::length($total);

		$current = Strings::padLeft($current, $total_length, '0');
		$total   = Strings::padLeft($total,   $total_length, '0');

		     if ($total) { $percent = round($current / $total * 100, 2); }
		else             { $percent = 0;                                 }

		$percent = number_format($percent, 2);
		$percent = Strings::padLeft($percent, 6);

		$passed = Floats::ceil($percent / (100 / $cells));

		$progress = Strings::repeat($cell, $passed);
		$progress = Strings::padRight($progress, $cells);

		if ($label_current !== null) { $label_current = ' ' . $label_current; }
		if ($label_total   !== null) { $label_total   = ' ' . $label_total;   }
		if ($postfix       !== null) { $postfix       = ' ' . $postfix;       }

		$message = "($current$label_current/$total$label_total) [$progress] $percent%$postfix";
		$message = Strings::padRight($message, static::cols(), ' ');

		static::write($message, false, true);

		if ($newline_on_complete and $current == $total)
		{
			static::write();
		}
	}
}

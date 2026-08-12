<?php

namespace Stackstra\Etc;

use Stackstra\Types\Strings;

class MIME
{
	public static function isImage($string): bool { return Strings::startsWith($string, 'image/'); }
	public static function isAudio($string): bool { return Strings::startsWith($string, 'audio/'); }
}
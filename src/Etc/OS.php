<?php

namespace Stackstra\Etc;

class OS
{
	const bool IS_WINDOWS = PHP_OS_FAMILY === 'Windows';
	const bool IS_BSD     = PHP_OS_FAMILY === 'BSD';
	const bool IS_DARWIN  = PHP_OS_FAMILY === 'Darwin';
	const bool IS_SOLARIS = PHP_OS_FAMILY === 'Solaris';
	const bool IS_LINUX   = PHP_OS_FAMILY === 'Linux';

	const bool IS_UNKNOWN = !self::IS_WINDOWS && !self::IS_BSD && !self::IS_DARWIN && !self::IS_SOLARIS && !self::IS_LINUX;
}

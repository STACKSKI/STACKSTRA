<?php

namespace Stackstra\Etc;

class Password
{
	# Use the bcrypt algorithm (default as of PHP 5.5.0). Note that this constant is designed to change
	# over time as new and stronger algorithms are added to PHP. For that reason, the length of the result
	# from using this identifier can change over time. Therefore, it is recommended to store the result in
	# a database column that can expand beyond 60 characters (255 characters would be a good choice)
	static string $algo_default = PASSWORD_DEFAULT;

	# Use the CRYPT_BLOWFISH algorithm to create the hash. This will produce a standard crypt() compatible
	# hash using the "$2y$" identifier. The result will always be a 60 character string, or FALSE on failure
	static string $algo_bcrypt  = PASSWORD_BCRYPT;

	public static function hash($password, $algo = PASSWORD_DEFAULT): string|null
	{
		$result = password_hash($password, $algo);

		if ($result === null or $result === false)
		{
			return null;
		}
		
		return $result;
	}

	public static function isValid($password, $hash): bool
	{
		return password_verify($password, $hash);
	}
}
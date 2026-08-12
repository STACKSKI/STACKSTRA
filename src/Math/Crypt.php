<?php

namespace Stackstra\Math;

use Stackstra\Types\Items;
use Stackstra\Types\Strings;

use Throwable;

class Crypt
{
	public static function algorithms()
	{
		return hash_algos();
	}

	/**
	 * @param int $length number of bytes to generate
	 *
	 * @return string|null null if the CSPRNG couldn't gather enough entropy
	 */
	public static function randBytes(int $length = 16): ?string
	{
		try
		{
			$rand = random_bytes($length);
		}
		catch (Throwable $e)
		{
			# if it was not possible to gather sufficient entropy
			
			return null;
		}

		return $rand;
	}

	/**
	 * @param int $min
	 * @param int $max
	 *
	 * @return int|null null if the CSPRNG couldn't gather enough entropy
	 */
	public static function randNumber(int $min, int $max): ?int
	{
		try
		{
			$rand = random_int($min, $max);
		}
		catch (Throwable $e)
		{
			# if it was not possible to gather sufficient entropy
			
			return null;
		}
		
		return $rand;
	}

	/**
	 * @param string      $password
	 * @param string|null $salt        random bytes generated when omitted
	 * @param int         $salt_length
	 * @param string      $algorithm
	 *
	 * @return string "$hash:$salt"
	 */
	public static function crypt(string $password, ?string $salt = null, int $salt_length = 16, string $algorithm = APP_CRYPT_HASH_ALGORITHM_DEFAULT): string
	{
		if ($salt === null)
		{
			$salt = static::randBytes($salt_length);
		}


		$hash = static::hash($password, $salt, $algorithm);

		return "$hash:$salt";
	}

	public static function hash($password, $salt, $algorithm = APP_CRYPT_HASH_ALGORITHM_DEFAULT)
	{
		# algorithm / chars / hash example (long hashes are cropped)
		#
		# md2           32 a9046c73e00331af68917d3804f70655
		# md4           32 866437cb7a794bce2b727acc0362ee27
		# md5           32 5d41402abc4b2a76b9719d911017c592
		# sha1          40 aaf4c61ddcc5e8a2dabede0f3b482cd9aea9434d
		# sha256        64 2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e730
		# sha384        96 59e1748777448c69de6b800d7a33bbfb9ff1b463e44354c3553
		# sha512       128 9b71d224bd62f3785d96d46ad3ea3d73319bfbc2890caadae2d
		# ripemd128     32 789d569f08ed7055e94b4289a4195012
		# ripemd160     40 108f07b8382412612c048d07d13f814118445acd
		# ripemd256     64 cc1d2594aece0a064b7aed75a57283d9490fd5705ed3d66bf9a
		# ripemd320     80 eb0cf45114c56a8421fbcb33430fa22e0cd607560a88bbe14ce
		# whirlpool    128 0a25f55d7308eca6b9567a7ed3bd1b46327f0f1ffdc804dd8bb
		# tiger128,3    32 a78862336f7ffd2c8a3874f89b1b74f2
		# tiger160,3    40 a78862336f7ffd2c8a3874f89b1b74f2f27bdbca
		# tiger192,3    48 a78862336f7ffd2c8a3874f89b1b74f2f27bdbca39660254
		# tiger128,4    32 1c2a939f230ee5e828f5d0eae5947135
		# tiger160,4    40 1c2a939f230ee5e828f5d0eae5947135741cd0ae
		# tiger192,4    48 1c2a939f230ee5e828f5d0eae5947135741cd0aefeeb2adc
		# snefru        64 7c5f22b1a92d9470efea37ec6ed00b2357a4ce3c41aa6e28e3b
		# gost          64 a7eb5d08ddf2363f1ea0317a803fcef81d33863c8b2f9f6d7d1
		# adler32        8 062c0215
		# crc32          8 3d653119
		# crc32b         8 3610a686
		# haval128,3    32 85c3e4fac0ba4d85519978fdc3d1d9be
		# haval160,3    40 0e53b29ad41cea507a343cdd8b62106864f6b3fe
		# haval192,3    48 bfaf81218bbb8ee51b600f5088c4b8601558ff56e2de1c4f
		# haval224,3    56 92d0e3354be5d525616f217660e0f860b5d472a9cb99d6766be
		# haval256,3    64 26718e4fb05595cb8703a672a8ae91eea071cac5e7426173d4c
		# haval128,4    32 fe10754e0b31d69d4ece9c7a46e044e5
		# haval160,4    40 b9afd44b015f8afce44e4e02d8b908ed857afbd1
		# haval192,4    48 ae73833a09e84691d0214f360ee5027396f12599e3618118
		# haval224,4    56 e1ad67dc7a5901496b15dab92c2715de4b120af2baf661ecd92
		# haval256,4    64 2d39577df3a6a63168826b2a10f07a65a676f5776a0772e0a87
		# haval128,5    32 d20e920d5be9d9d34855accb501d1987
		# haval160,5    40 dac5e2024bfea142e53d1422b90c9ee2c8187cc6
		# haval192,5    48 bbb99b1e989ec3174019b20792fd92dd67175c2ff6ce5965
		# haval224,5    56 aa6551d75e33a9c5cd4141e9a068b1fc7b6d847f85c3ab16295
		# haval256,5    64 348298791817d5088a6de6c1b6364756d404a50bd64e645035f

		return hash($algorithm, $password . $salt);
	}

	/**
	 * @param int|string  $user_id
	 * @param string|null $salt      random bytes generated when omitted
	 * @param string      $algorithm
	 *
	 * @return array{0: string, 1: string|null} [hash, salt], empty hash if $user_id is falsy
	 */
	public static function autologin(int|string $user_id, ?string $salt = null, string $algorithm = APP_CRYPT_HASH_ALGORITHM_DEFAULT): array
	{
		if (!$user_id)
		{
			return ['', $salt];
		}

		if ($salt === null)
		{
			$salt = static::randBytes();
		}

		return [static::hash($user_id, $salt, $algorithm), $salt];
	}

	public static function isAlgorithmExist($algorithm)
	{
		return Items::contains(static::algorithms(), $algorithm);
	}

	/**
	 * @param string $password
	 * @param string $crypt    "$hash:$salt" as produced by crypt()
	 *
	 * @return bool
	 */
	public static function isValidCrypt(string $password, string $crypt): bool
	{
		[$hash, $salt] = static::explode($crypt, limit: 2);

		return static::isValidPassword($password, $hash, $salt);
	}

	public static function isValidHash($hash_known, $hash_user)
	{
		return hash_equals($hash_known, $hash_user);
	}

	public static function isValidPassword($password, $hash, $salt)
	{
		return static::isValidHash(static::hash($password, $salt), $hash);
	}

	/**
	 * @param int|string $user_id
	 * @param string     $autologin token to verify against
	 * @param string     $salt      salt used when the token was generated
	 *
	 * @return bool
	 */
	public static function isValidAutologin(int|string $user_id, string $autologin, string $salt): bool
	{
		if (!$user_id)
		{
			return false;
		}

		[$hash] = static::autologin($user_id, $salt);

		return static::isValidHash($hash, $autologin);
	}

	public static function explode($string, $limit = PHP_INT_MAX)
	{
		return explode(':', $string, $limit);
	}

	public static function crc($data)
	{
		# replace with crc64 if you are faced with collisions

		return static::crc32($data);
	}

	/**
	 * @param string $data
	 *
	 * @return int CRC-16/MODBUS checksum
	 */
	public static function crc16(string $data): int
	{
		$crc = 0xFFFF;

		$data_length = Strings::size($data);

		for ($i = 0; $i < $data_length; $i++)
		{
			$crc ^= ord($data[$i]);

			for ($j = 8; $j != 0; $j--)
			{
				if (($crc & 0x0001) != 0)
				{
					$crc >>= 1;
					$crc ^= 0xA001;
				}
				else
				{
					$crc >>= 1;
				}
			}
		}

		return $crc;
	}

	public static function crc32($data)
	{
		return crc32($data);
	}

	/**
	 * # http://www.php.net/manual/ru/function.crc32.php#111699
	 *
	 * crc64('php');         # afe4e823e7cef190
	 * crc64('php', '0x%x'); # 0xafe4e823e7cef190
	 * crc64('php', '0x%X'); # 0xAFE4E823E7CEF190
	 * crc64('php', '%d');   # -5772233581471534704 signed int
	 * crc64('php', '%u');   # 12674510492238016912 unsigned int
	 *
	 * @param        $data
	 * @param string $format
	 *
	 * @return string
	 */
	public static function crc64($data, $format = '%x')
	{
		# warning
		# -------
		# int64 in PHP is SIGNED and can be overflowed by UNSIGNED
		# results, so return strings or repack SIGNED as UNSIGNED later
		# and of course you need x64 PHP (OS) for it

		global $crc64tab;

		if ($crc64tab === null)
		{
			$crc64tab = [];


			# ECMA polynomial

			$poly64rev = (0xC96C5795 << 32) | 0xD7870F42;


			# ISO polynomial
			# $poly64rev = (0xD8 << 56);

			for ($i = 0; $i < 256; $i++)
			{
				for ($part = $i, $bit = 0; $bit < 8; $bit++)
				{
					if ($part & 1)
					{
						$part = (($part >> 1) & ~(0x8 << 60)) ^ $poly64rev;
					}
					else
					{
						$part = ($part >> 1) & ~(0x8 << 60);
					}
				}

				$crc64tab[$i] = $part;
			}
		}


		$data_length = Strings::size($data);

		for ($crc = 0, $i = 0; $i < $data_length; $i++)
		{
			$crc = $crc64tab[($crc ^ ord($data[$i])) & 0xff] ^ (($crc >> 8) & ~(0xff << 56));
		}

		return sprintf($format, $crc);
	}
}
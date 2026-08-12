<?php

namespace Stackstra\Filesystem;

use function Stackstra\false_to_null;

use Stackstra\Types\Strings;

class File
{
	/**
	 * @param string $path
	 * @param string $mode
	 *
	 * @return FileObject
	 */
	public static function open(string $path, string $mode)
	{
		return new FileObject($path, $mode);
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function create(string $path): bool
	{
		return touch($path);
	}

	/**
	 * @param string $path path to the file
	 *
	 * @return string|null
	 */
	public static function read(string $path): ?string
	{
		$data = file_get_contents($path);

		return false_to_null($data);
	}

	/**
	 * @param string $path
	 * @param string $data
	 * @param int    $flags
	 *
	 * @return int|null
	 */
	public static function write(string $path, string $data, int $flags = 0, bool $recursive = false): ?int
	{
		if ($recursive)
		{
			$directory = File::directory($path);

			if (!Directory::isExist($directory))
			{
				if (!Directory::create($directory, recursive: true))
				{
					return null;
				}
			}
		}

		$data = file_put_contents($path, $data, $flags);

		return false_to_null($data);
	}

	/**
	 * @param string $file_path
	 * @param string $data
	 * @param int    $flags
	 *
	 * @return int|null
	 */
	public static function rewrite(string $file_path, string $data, int $flags = 0): ?int
	{
		return self::write($file_path, $data, $flags);
	}

	/**
	 * Appends content to an existing file
	 *
	 * @param string $path path to the file
	 * @param string $data string, array or stream resource
	 *
	 * @return int|null
	 */
	public static function append(string $path, string $data): ?int
	{
		return self::write($path, $data, FILE_APPEND);
	}

	public static function appendLine(string $path, string $data): ?int
	{
		return self::append($path, $data . PHP_EOL);
	}

	public static function pathReal(string $path): ?string
	{
		return false_to_null(realpath($path));
	}

	public static function pathCombine(...$arguments): string
	{
		foreach ($arguments as $index => $argument)
		{
			if ($argument === null)
			{
				unset($arguments[$index]);

				continue;
			}

			     if ($index == 0) { $arguments[$index] = Strings::trimRight($argument, [DIRECTORY_SEPARATOR     ]); }
			else                  { $arguments[$index] = Strings::trim     ($argument, [DIRECTORY_SEPARATOR, '.']); }
		}

		return implode(DIRECTORY_SEPARATOR, $arguments);
	}

	public static function createTmp(string $prefix = __NAMESPACE__): ?string
	{
		$path = tempnam(Directory::tmp(), $prefix);

		return false_to_null($path);
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function isExist(string $path): bool
	{
		return file_exists($path) && is_file($path);
	}

	public static function isLink(string $path): bool
	{
		return is_link($path);
	}

	/**
	 * Gets file modification time as a Unix timestamp integer value
	 *
	 * $result = self::timestamp('file.txt'); # int(1409333664)
	 *
	 * @param string $path path to the file
	 *
	 * @return int|null
	 */
	public static function timestamp(string $path): ?int
	{
		$timestamp = filemtime($path);

		return false_to_null($timestamp);
	}

	/**
	 * @param string      $path
	 * @param string|null $part
	 *
	 * @return string[]|string
	 */
	public static function parts(string $path, ?string $part = null): array|string
	{
		     if ($part === null) { return pathinfo($path);        }
		else                     { return pathinfo($path, $part); }
	}

	/**
	 * "/path/to/file.txt" => "/path/to"
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function directory(string $path): string
	{
		return self::parts($path, PATHINFO_DIRNAME);
	}

	/**
	 * "/path/to/file.txt" => "txt"
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function extension(string $path): string
	{
		return self::parts($path, PATHINFO_EXTENSION);
	}

	/**
	 * "/path/to/file.txt" => "file" OR "file.txt"
	 *
	 * @param string $path
	 * @param bool   $include_extension
	 *
	 * @return string
	 */
	public static function name(string $path, bool $include_extension = true): string
	{
		if ($include_extension)
		{
			return self::parts($path, PATHINFO_BASENAME);
		}

		return self::parts($path, PATHINFO_FILENAME);
	}

	/**
	 * @param string $path
	 *
	 * @return int|null
	 */
	public static function size(string $path): ?int
	{
		return false_to_null(filesize($path));
	}

	/**
	 * @param string $file_path
	 *
	 * @return int
	 */
	public static function sizeZip(string $file_path): int
	{
		$size = 0;

		$resource = zip_open($file_path);

		while ($dir_resource = zip_read($resource))
		{
			$size += zip_entry_filesize($dir_resource);
		}

		zip_close($resource);

		return $size;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function mime(string $path): string
	{
		return mime_content_type($path);
	}

	/**
	 * @en Changes a file extension
	 *
	 * $result = self::changeExtension('my.txt', 'html'); # bool(true) on success and bool(false) otherwise
	 *
	 * @param string $path
	 * @param string $extension_new new extension (dot is not required)
	 *
	 * @return bool
	 */
	public static function changeExtension(string $path, string $extension_new): bool
	{
		$name_new = self::name($path, false) . '.' . $extension_new;

		return self::rename($path, $name_new);
	}

	public static function changeName(string $path, string $name_new): bool
	{
		return self::rename($path, $name_new);
	}

	public static function move(string $path_from, string $path_to): bool
	{
		return self::isExist($path_from) && rename($path_from, $path_to);
	}

	public static function rename(string $path, string $name_new): bool
	{
		$path_new = self::directory($path) . DIRECTORY_SEPARATOR . $name_new;

		return self::move($path, $path_new);
	}

	public static function copy(string $path_from, string $path_to): bool
	{
		return copy($path_from, $path_to);
	}

	public static function remove(string $path)
	{
		return unlink($path);
	}

	/**
	 * Calculates the SHA-1 hash of a file
	 *
	 * $result = self::hashSHA1('file.txt'); # string(40) "f8de2ea7a87ac87f97dc61110de03234f176454c"
	 *
	 * @param string $path file path
	 *
	 * @return string
	 */
	public static function hashSHA1($path)
	{
		return sha1_file($path);
	}

	/**
	 * Calculates the MD5 hash of a file
	 *
	 * $result = self::hashMD5('file.txt'); # string(32) "5cbdabd2d7f9cf5a193f45e3c57ec062"
	 *
	 * @param string $path file path
	 *
	 * @return string
	 */
	public static function hashMD5($path)
	{
		return md5_file($path);
	}

	public static function hashCRC32($path)
	{
		$hash = hash_file('crc32b', $path);

		$array = unpack('N', pack('H*', $hash));

		return $array[1];
	}

}

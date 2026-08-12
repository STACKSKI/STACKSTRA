<?php

namespace Stackstra\Filesystem;

use function Stackstra\false_to_null;

use Stackstra\Etc\OS;
use Stackstra\Types\Items;
use Stackstra\Types\Strings;
use Stackstra\Exceptions\Exceptions;

class Directory
{
	public static function current(): ?string
	{
		return false_to_null(getcwd());
	}

	public static function change(string $directory): bool
	{
		return chdir($directory);
	}

	public static function tmp(): string
	{
		return sys_get_temp_dir();
	}

	/**
	 * @param string $path
	 *
	 * @return string[]
	 */
	public static function files(string $path): array
	{
		$files = scandir($path);
		$files = array_diff($files, ['.', '..']);

		return array_values($files);
	}

	/**
	 * Create a directory
	 *
	 * @param string $path
	 * @param int    $mode
	 * @param bool   $recursive
	 * @param bool   $skip_if_exists
	 * @param bool   $create_or_fail
	 *
	 * @return bool
	 */
	public static function create(string $path, int $mode = 0755, bool $recursive = false, bool $skip_if_exists = false, bool $create_or_fail = false): bool
	{
		if ($skip_if_exists and self::isExist($path))
		{
			return true;
		}

		$result = mkdir($path, $mode, $recursive);

		if ($create_or_fail and $result === false)
		{
			Exceptions::error("unable to create the following directory: $path");
		}

		return $result;
	}

	public static function createOrFail(string $path, int $mode = 0755, bool $recursive = false, bool $skip_if_exists = false): bool
	{
		return self::create($path, mode: $mode, recursive: $recursive, skip_if_exists: $skip_if_exists, create_or_fail: true);
	}

	/**
	 * Remove a directory
	 *
	 * $result = self::remove('/srv/http/directory/'); # bool(true) on success and bool(false) otherwise
	 * $result = self::remove('/srv/http/file.txt');   # bool(true) on success and bool(false) otherwise
	 *
	 * @param string $path
	 * @param bool   $recursively
	 *
	 * @return bool
	 */
	public static function remove(string $path, bool $recursively = false): bool
	{
		if ($recursively)
		{
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $item)
			{
				/** @var $item \DirectoryIterator */
				$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
			}
		}

		return rmdir($path);
	}

	/**
	 * @param string $path file path
	 *
	 * @return int
	 */
	public static function size(string $path): int
	{
		$size = 0;

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file)
		{
			/** @var \SplFileInfo $file */

			$size += $file->getSize();
		}

		return $size;
	}

	public static function isExist(string $path): bool
	{
		return file_exists($path) && is_dir($path);
	}

	public static function isEmpty(string $path): bool
	{
		return !self::files($path);
	}

	public static function assertExists(string $path)
	{
		if (!static::isExist($path))
		{
			Exceptions::error("the following directory does not exist: `$path`");
		}
	}

	public static function name(string $path): string
	{
		return File::name($path);
	}

	public static function parentName(string $path): string
	{
		return File::directory($path);
	}

	public static function move(string $path_from, string $path_to): bool
	{
		return self::isExist($path_from) && rename($path_from, $path_to);
	}

	public static function spaceTotal(string $path): ?float { return false_to_null(disk_total_space($path)); }
	public static function spaceFree (string $path): ?float { return false_to_null(disk_free_space ($path)); }

	public static function spaceUsed(string $path): ?float
	{
		$total = static::spaceTotal($path);
		$free  = static::spaceFree($path);

		if ($total === null or $free === null)
		{
			return null;
		}

		return $total - $free;
	}

	public static function pathCombine(...$arguments)
	{
		$arguments = Items::flat($arguments);
		$arguments = Items::removeNull($arguments);
		$arguments = Items::reindex($arguments);
		$arguments = array_map(fn($chunk) => Strings::trim($chunk, [DIRECTORY_SEPARATOR]), $arguments);

		$path = implode(DIRECTORY_SEPARATOR, $arguments);

		if ((OS::IS_LINUX || OS::IS_BSD) && !Strings::startsWith($path, '/'))
		{
			$path = DIRECTORY_SEPARATOR . $path;
		}

		return $path;
	}

	public static function clearCaches(): void
	{
		clearstatcache();
	}
}

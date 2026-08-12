<?php

namespace Stackstra\Filesystem;

use Stackstra\Types\Items;
use function Stackstra\false_to_null;
use function Stackstra\get;

use SplFileObject;
use SplFileInfo;

use Stackstra\Filesystem\Traits\HasPath;
use Stackstra\Exceptions\Exceptions;

class FileObject
{
	use HasPath;

	# FILE MODES

	# [r]:  Open for reading only; place the file pointer at the beginning of the file.
	# [r+]: Open for reading and writing; place the file pointer at the beginning of the file.
	# [w]:  Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
	# [w+]: Open for reading and writing; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
	# [a]:  Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek() has no effect, writes are always appended.
	# [a+]: Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek() only affects the reading position, writes are always appended.
	# [x]:  Create and open for writing only; place the file pointer at the beginning of the file. If the file already exists, the fopen() call will fail by returning false and generating an error of level E_WARNING. If the file does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
	# [x+]: Create and open for reading and writing; otherwise it has the same behavior as 'x'.
	# [c]:  Open the file for writing only. If the file does not exist, it is created. If it exists, it is neither truncated (as opposed to 'w'),
	#       nor the call to this function fails (as is the case with 'x'). The file pointer is positioned on the beginning of the file.
	#       This may be useful if it's desired to get an advisory lock (see flock()) before attempting to modify the file, as using 'w' could
	#       truncate the file before the lock was obtained (if truncation is desired, ftruncate() can be used after the lock is requested).
	# [c+]: Open the file for reading and writing; otherwise it has the same behavior as 'c'.
	# [e]:  Set close-on-exec flag on the opened file descriptor. Only available in PHP compiled on POSIX.1-2008 conform systems.

	const array MODES =
	[
		'r', 'r+', 'rb', 'r+b',
		'w', 'w+', 'wb', 'w+b',
		'a', 'a+', 'ab', 'a+b',
		'x', 'x+', 'xb', 'x+b',
		'c', 'c+', 'cb', 'c+b',
		'e'
	];

	const int FLAG_DROP_NEW_LINE = SplFileObject::DROP_NEW_LINE; # drop newlines at the end of a line.
	const int FLAG_READ_AHEAD    = SplFileObject::READ_AHEAD;    # read on rewind/next.
	const int FLAG_SKIP_EMPTY    = SplFileObject::SKIP_EMPTY;    # skip empty lines in the file, this requires the {@see READ_AHEAD} flag to work as expected.
	const int FLAG_READ_CSV      = SplFileObject::READ_CSV;      # read lines as CSV rows

	const int FLAGS_CSV_DEFAULT = self::FLAG_READ_CSV | self::FLAG_SKIP_EMPTY | self::FLAG_READ_AHEAD | self::FLAG_DROP_NEW_LINE;

	const string CSV_SEPARATOR = ',';
	const string CSV_ENCLOSURE = '"';
	const string CSV_ESCAPE    = '\\';

	protected ?SplFileObject $file = null;

	protected ?string $mode = null;

	public function __construct(string|array $path, ?string $mode = null)
	{
		$this->path = $path;

		if ($mode !== null)
		{
			$this->open(mode: $mode);
		}
	}

	public function __destruct()
	{
		$this->close();
	}

	public static function make(string|array|self $path, ?string $mode = null): self
	{
		if ($path instanceof self)
		{
			if ($mode !== null)
			{
				$path->open($mode);
			}

			return $path;
		}

		if (is_array($path))
		{
			$path = File::pathCombine(...$path);
		}

		return new self(path: $path, mode: $mode);
	}

	/**
	 * @return SplFileObject|null
	 */
	public function get(): ?SplFileObject
	{
		return $this->file;
	}

	/**
	 * @param string $mode
	 *
	 * @return self
	 */
	public function open(string $mode): self
	{
		$this->assertModeValid($mode);

		$this->file = new SplFileObject($this->path(), $mode);
		$this->file->setCsvControl(self::CSV_SEPARATOR, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
		$this->mode = $mode;

		return $this;
	}

	public function openIfClosed(string $mode): self
	{
		if (!$this->mode())
		{
			return self::open($mode);
		}

		return $this;
	}

	public function reopen(?string $mode = null): bool
	{
		$this->assertFileOpened();

		if ($mode === null)
		{
			$mode = $this->mode();
		}
		else
		{
			$this->assertModeValid($mode);
		}

		$this->close();
		$this->open(mode: $mode);

		return true;
	}


	/**
	 * Note that this class has a private (and thus, not documented) property that holds the file pointer.
	 * Combine this with the fact that there is no method to close the file handle, and you get into situations
	 * where you are not able to delete the file with unlink(), etc., because an SplFileObject still has a handle open.
	 *
	 * To get around this issue, delete the SplFileObject like this: "$object = null;"
	 */
	public function close()
	{
		$this->file = null;
		$this->mode = null;
	}

	public function mode(): ?string
	{
		return $this->mode;
	}

	public function resize(int $size): bool
	{
		return $this->file->ftruncate($size);
	}

	public function truncate(int $size = 0): bool
	{
		return $this->resize($size);
	}

	public function flush(): bool
	{
		return $this->file->fflush();
	}

	public function getStat(): array
	{
		return $this->file->fstat();
	}

	protected function lock(int $mask): bool
	{
		return $this->file->flock($mask);
	}

	public function lockForRead(bool $non_blocking = false): bool
	{
		     if ($non_blocking) { return $this->lock(LOCK_SH | LOCK_NB); }
		else                    { return $this->lock(LOCK_SH); }
	}

	public function lockForWrite(bool $non_blocking = false): bool
	{
		     if ($non_blocking) { return $this->lock(LOCK_EX | LOCK_NB); }
		else                    { return $this->lock(LOCK_EX); }
	}

	public function unlock(): bool
	{
		return $this->file->flock(LOCK_UN);
	}

	public function create(?int $mtime = null, ?int $atime = null): bool
	{
		return touch($this->path(), ...func_get_args());
	}

	public function createIfNotExist(): self
	{
		if (!$this->isExist())
		{
			$this->create();
		}

		return $this;
	}

	public function write(string $string, ?int $length = null): int
	{
		return $this->file->fwrite(...func_get_args());
	}

	public function writeJSON(mixed $data, int $flags = JSON_PRETTY_PRINT): int
	{
		$json = json_encode($data, flags: $flags);

		return $this->write($json);
	}

	public function writeLine(string $string, ?int $length = null): int
	{
		if (func_num_args() >= 2)
		{
			return $this->write($string . PHP_EOL, $length);
		}

		return $this->write($string . PHP_EOL);
	}

	public function read(?int $length = null): ?string
	{
		if ($length === null)
		{
			$length = filesize($this->path());

			if ($length === false)
			{
				Exceptions::error("unable to figure out the file size of the following file: {$this->path()}");
			}
		}

		if ($length === 0)
		{
			return '';
		}

		$result = $this->file->fread($length);

		return false_to_null($result);
	}

	public function readJSON(?bool $associative = true, ?array $columns = null, ?string $path = null): mixed
	{
		$json = $this->read();

		if ($json === null)
		{
			return null;
		}

		$data = json_decode($json, associative: $associative);

		if (is_string($path))
		{
			$data = data_get($data, $path);
		}

		if (is_array($columns))
		{
			foreach ($data as $index => $value)
			{
				$data[$index] = Items::only($value, $columns);
			}
		}

		return $data;
	}

	public function readCSV(?array $columns_all = null, ?array $columns_keep = null, ?callable $filter = null, bool $skip_headers = false, int $flags = self::FLAGS_CSV_DEFAULT, ?string $index = null): array
	{
		$this->file->setFlags(flags: $flags);

		$map = null;

		if (is_array($columns_all))
		{
			$map = [];
		}

		if ($columns_keep === null)
		{
			$columns_keep = $columns_all;
		}

		if (is_array($columns_keep))
		{
			foreach ($columns_keep as $column_keep)
			{
				$key = array_search($column_keep, $columns_all);

				if ($key === false)
				{
					Exceptions::error("unable to find the following column: `$column_keep`");
				}

				$map[$key] = $column_keep;
			}
		}


		$data = [];

		while (!$this->file->eof())
		{
			// read the line
			$values = $this->file->fgetcsv();

			if ($values === false)
			{
				break;
			}

			// columns remap (if available)
			if ($map !== null)
			{
				$result = [];

				foreach ($values as $key => $value)
				{
					if (isset($map[$key]))
					{
						$result[$map[$key]] = $value;
					}
				}

				$values = $result;
			}

			// custom callback (if available)
			if ($filter)
			{
				$values = $filter($values);

				if ($values === false)
				{
					continue;
				}
			}

			     if ($index) { $data[$values[$index]] = $values; }
			else             { $data[] = $values; }
		}

		if ($skip_headers)
		{
			array_shift($data);
		}

		return $data;
	}

	//public function readCSVArray(?callable $filter = null, int $flags = self::FLAGS_CSV_DEFAULT): array
	//{
	//	$this->file->setFlags(flags: $flags);
	//
	//	$data = [];
	//
	//	while (!$this->file->eof())
	//	{
	//		$values = $this->file->fgetcsv();
	//
	//		if ($values === false)
	//		{
	//			break;
	//		}
	//
	//		if ($filter)
	//		{
	//			$values = $filter($values);
	//
	//			if ($values === false)
	//			{
	//				continue;
	//			}
	//		}
	//
	//		$data[] = $values;
	//	}
	//
	//	return $data;
	//}
	//
	//public function readCSVcolumns(?array $columns_all = null, ?array $columns_keep = null): array
	//{
	//	$map = [];
	//
	//	if ($columns_keep === null)
	//	{
	//		$columns_all = $columns_keep;
	//	}
	//
	//	foreach ($columns_keep as $column_keep)
	//	{
	//		$index = array_search($column_keep, $columns_all);
	//
	//		if ($index === false)
	//		{
	//			Exceptions::error("unable to find the following column: `$column_keep`");
	//		}
	//
	//		$map[$index] = $column_keep;
	//	}
	//
	//	return $this->readCSVArray(function ($values) use ($map)
	//	{
	//		$result = [];
	//
	//		foreach ($values as $index => $value)
	//		{
	//			if (isset($map[$index]))
	//			{
	//				$result[$map[$index]] = $value;
	//			}
	//		}
	//
	//		return $result;
	//	});
	//}

	public function readChar(): ?string { return false_to_null($this->file->fgetc()); }
	public function readLine(): ?string { return false_to_null($this->file->fgets()); }

	public function offsetGet(): ?int
	{
		return false_to_null($this->file->ftell());
	}

	public function offsetSet(int $offset, ?int $mode = SEEK_SET): bool
	{
		return $this->file->fseek($offset, $mode) === 0;
	}

	public function offsetSetOEF(): bool
	{
		return $this->offsetSet(offset: 0, mode: SEEK_END);
	}

	public function offsetReset()
	{
		$this->file->rewind();
	}

	public function remove(): bool
	{
		static::close();

		return unlink($this->path());
	}

	public function move(string|array|self|DirectoryObject $to): ?static
	{
		if (is_string($to) or is_array($to))
		{
			$to = static::make($to);
		}
		else if ($to instanceof DirectoryObject)
		{
			# move file to directory
			$to = static::make([$to->path(), $this->name()]);
		}

		$this->close();

		if (!File::move($this->path(), $to->path()))
		{
			return null;
		}

		return $to;
	}

	# FLAGS

	public function setFlags(int $flags)
	{
		$this->file->setFlags($flags);
	}

	public function getFlags(): int
	{
		return $this->file->getFlags();
	}

	public function  enable(int $flag): self { $this->setFlags($this->getFlags() |  $flag); return $this; }
	public function disable(int $flag): self { $this->setFlags($this->getFlags() & ~$flag); return $this; }

	public function enableDropNewLine():  self { return $this->enable(self::FLAG_DROP_NEW_LINE); }
	public function enableReadAhead():    self { return $this->enable(self::FLAG_READ_AHEAD);    }
	public function enableSkipEmpty():    self { return $this->enable(self::FLAG_SKIP_EMPTY);    }
	public function enableReadCsv():      self { return $this->enable(self::FLAG_READ_CSV);      }

	public function disableDropNewLine():  self { return $this->disable(self::FLAG_DROP_NEW_LINE); }
	public function disableReadAhead():    self { return $this->disable(self::FLAG_READ_AHEAD);    }
	public function disableSkipEmpty():    self { return $this->disable(self::FLAG_SKIP_EMPTY);    }
	public function disableReadCsv():      self { return $this->disable(self::FLAG_READ_CSV);      }


	# CSV

	public function csvSettings(string $separator = self::CSV_SEPARATOR, string $enclosure = self::CSV_ENCLOSURE, string $escape = self::CSV_ESCAPE): array
	{
		if (func_num_args())
		{
			$this->file->setCsvControl($separator, $enclosure, $escape);
		}

		return $this->file->getCsvControl();
	}

	/**
	 * @return array[]
	 */
	public function csvRead(): array
	{
		$result = [];

		while (!$this->file->eof())
		{
			$data = $this->file->fgetcsv();

			if ($data === false)
			{
				Exceptions::warning('unable to read a CSV line');

				$data = null;
			}

			$result[] = $data;
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	public function csvReadLine(): ?array
	{
		return false_to_null($this->file->fgetcsv());
	}

	public function csvReadCallback(\Closure $callback): void
	{
		while (!$this->file->eof())
		{
			$data = $this->file->fgetcsv();

			if ($data === false)
			{
				Exceptions::warning('unable to read a CSV line');

				$data = null;
			}

			$callback($data);
		}
	}

	public function writeCSV(array $items): ?int
	{
		return false_to_null($this->file->fputcsv($items));
	}

	public function writeCSVBulk(array $items_list): int
	{
		$counter = 0;

		foreach ($items_list as $items)
		{
			$result = $this->file->fputcsv((array) $items);

			if ($result === false)
			{
				Exceptions::warning('unable to write a CSV line');
			}
			else
			{
				$counter += $result;
			}
		}

		return $counter;
	}


	# FILE INFO

	/**
	 * @return string "/usr/bin/php"
	 */
	public function path(): string
	{
		return $this->path; /*$this->file->getPathname();*/
	}

	public function pathReal(): ?string
	{
		return File::pathReal($this->path());
	}

	/**
	 * @return string "/usr/bin/php" => "/usr/bin"
	 */
	public function pathDirectory(): string
	{
		return File::directory($this->path());
	}

	/**
	 * @return string "/path/to/foo.txt" => "txt"
	 */
	public function extension(): string
	{
		return $this->file->getExtension();
	}

	/**
	 * @param bool $include_extension
	 *
	 * @return string "/path/to/foo.txt" => "foo.txt" OR "/path/to/foo.txt" => "foo"
	 */
	public function name(bool $include_extension = true): string
	{
		if ($include_extension)
		{
			return $this->file->getBasename();
		}

		return $this->file->getBasename('.' . $this->file->getExtension());
	}

	public function linkTarget(): string
	{
		return $this->file->getLinkTarget();
	}

	public function linkInfo(?string $property = null): array|int|null
	{
		$result = false_to_null(lstat($this->path()));

		if ($property !== null)
		{
			return get($result, $property);
		}

		return $result;
	}

	public function linkTimestampAccess(): ?int { return static::linkInfo('atime'); } # atime - last accessed time, it gets updated when you open a file but also when a file is used for other operations like grep, sort, cat, head, tail and so on
	public function linkTimestampModify(): ?int { return static::linkInfo('mtime'); } # mtime - the last modified time, when you changed the content of the file
	public function linkTimestampChange(): ?int { return static::linkInfo('ctime'); } # ctime - change time, the last time the inode for that file has been changed (e.g. you changed permissions, or renamed the file)

	public function linkOwner(int|string $owner) { return lchown(static::path(), $owner); }
	public function linkGroup(int|string $group) { return lchgrp(static::path(), $group); }

	public function type():        ?string { return false_to_null($this->file->getType());  } # file, link, dir, block, fifo, char, socket or unknown
	public function size():        ?int    { return false_to_null($this->file->getSize());  } # in bytes
	public function ownerID():     ?int    { return false_to_null($this->file->getOwner()); }
	public function groupID():     ?int    { return false_to_null($this->file->getGroup()); }
	public function permissions(): ?int    { return false_to_null($this->file->getPerms()); }
	public function inode():       ?int    { return false_to_null($this->file->getInode()); }

	public function timestampAccess():   ?int { return false_to_null($this->file->getATime()); }
	public function timestampChange():   ?int { return false_to_null($this->file->getCTime()); }
	public function timestampModified(): ?int { return false_to_null($this->file->getMTime()); }


	/** @return SplFileInfo */
	public function info(): SplFileInfo { return $this->file->getFileInfo(); }

	/** @return SplFileInfo */
	public function directoryInfo(): SplFileInfo { return $this->file->getPathInfo(); }

	public function isEof():        bool { return $this->file->eof();          }
	public function isReadable():   bool { return $this->file->isReadable();   }
	public function isWritable():   bool { return $this->file->isWritable();   }
	public function isExecutable(): bool { return $this->file->isExecutable(); }
	public function isDirectory():  bool { return $this->file->isDir();        }
	public function isFile():       bool { return $this->file->isFile();       }
	public function isLink():       bool { return $this->file->isLink();       }
	public function isOpened():     bool { return $this->file !== null;        }
	public function isExist():      bool { return file_exists($this->path()); }

	public static function isModeValid(?string $mode): bool
	{
		if ($mode === null)
		{
			return false;
		}

		static $modes;

		if ($modes === null)
		{
			$modes = array_flip(self::MODES);
		}

		return isset($modes[$mode]);
	}

	public function assertExist(): self
	{
		return $this->assert($this->isExist(), "The following file does not exist: `{$this->path()}`");
	}

	protected function assertFilePathUpdated(): self
	{
		return $this->assert($this->path === $this->file->getPathname(), "{$this->path} does not match {$this->file->getPathname()}");
	}

	protected function assertFileOpened():     self { return $this->assert($this->isOpened(),          "the file hasn't been opened"); }
	protected function assertModeValid($mode): self { return $this->assert(static::isModeValid($mode), "invalid mode: `$mode`"); }

	protected function assert(mixed $value, string $message): self
	{
		if (!$value)
		{
			Exceptions::error($message);
		}

		return $this;
	}

	protected function assertNot(mixed $value, string $message): self
	{
		return self::assert(value: !$value, message: $message);
	}

	public function cacheReset(): self
	{
		# This function caches information about specific filenames, so you only need to call clearstatcache() if you are performing multiple operations
		# on the same filename and require the information about that particular file to not be cached.

		# Affected functions include:
		# stat(), lstat(), file_exists(), is_writable(), is_readable(), is_executable(), is_file(), is_dir(), is_link(), filectime(),
		# fileatime(), filemtime(), fileinode(), filegroup(), fileowner(), filesize(), filetype(), and fileperms().

		# TODO: test it
		clearstatcache(true, $this->path());

		return $this;
	}

	public function hashCRC32()
	{
		return hash_file('crc32b', $this->path());
	}
}

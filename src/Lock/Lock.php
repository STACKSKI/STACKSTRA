<?php

namespace Stackstra\Lock;

use Stackstra\Etc\PHP;
use Stackstra\Filesystem\File;
use Stackstra\Regexp\Regexp;

/**
 * This class is a classic Linux lock file implementation - https://dmorgan.info/posts/linux-lock-files/
 *
 * Main purposes:
 * - daemons: they store their process IDs inside the files, so other programs which know the path of
 *            the lock file are able to find them and send
 * - situations when you would like to prevent multi-process access to a resource/file/data
 *
 * @package rude
 */
class Lock
{
	/** @var false|resource */
	protected $file;

	protected string $path;
	protected string $mode;

	protected bool $is_locked = false;

	/**
	 * @param string|null $file_path defaults to a fresh temp file
	 * @param string      $file_mode fopen() mode used when the lock file is opened
	 */
	public function __construct(?string $file_path = null, string $file_mode = 'a+')
	{
		if ($file_path === null)
		{
			$file_path = File::createTmp('lock_');
		}

		$this->mode = $file_mode;
		$this->path = $file_path;
	}

	public function __destruct()
	{
		if (static::is_opened() and static::is_locked())
		{
			static::unlock();
		}
	}

	public function truncate(): bool
	{
		return ftruncate($this->file, 0) and rewind($this->file);
	}

	public function write(string $data): int|false
	{
		return fwrite($this->file, $data);
	}

	public function create(?string $file_path = null): bool
	{
		if ($file_path === null)
		{
			$file_path = $this->path;
		}

		if ($file_path)
		{
			return touch($file_path);
		}

		return false;
	}

	/**
	 * Unlocks any currently open file, then opens (creating if needed) $file_path/$file_mode
	 * or the instance's own path/mode, falling back to a namespaced temp path if neither is set
	 */
	protected function open(?string $file_path = null, ?string $file_mode = null): bool
	{
		static::unlock();

		if ($file_path) { $this->path = $file_path; }
		if ($file_mode) { $this->mode = $file_mode; }

		if (!$this->path)
		{
			$file_name = Regexp::keepAlphaNum(APP_NAMESPACE);
			$file_path = sys_get_temp_dir() . "/$file_name.lock";

			$this->path = $file_path;
		}


		$this->file = fopen($this->path, $this->mode);

		if ($this->file === false)
		{
			$this->file = null;

			return false;
		}

		return true;
	}

	protected function is_opened(): bool
	{
		return $this->file !== null;
	}

	protected function close(): bool
	{
		if ($this->file and fclose($this->file))
		{
			$this->file = null;

			return true;
		}

		return false;
	}

	protected function is_exist(?string $path = null): bool
	{
		if ($path === null)
		{
			$path = $this->path;
		}

		if ($path and file_exists($path))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks whether $pid (or the PID stored in the lock file) belongs to a still-running process
	 */
	protected function is_exist_pid(?int $pid = null): int|false
	{
		if ($pid === null)
		{
			$pid = file_get_contents($this->path);
		}

		if ($pid)
		{
			return posix_getpgid($pid);
		}

		return false;
	}

	/**
	 * Creates/opens the lock file if needed, writes this process's PID into it, then attempts a
	 * non-blocking exclusive flock(); fails immediately if another live process already holds it
	 */
	public function lock(): bool
	{
		if ($this->path)
		{
			if (static::is_exist() and static::is_exist_pid())
			{
				return false;
			}

			if (!static::is_exist()  and !static::create()) { return false; }
			if (!static::is_opened() and !static::open())   { return false; }

			$this->truncate();
			$this->write(PHP::pid());
		}


		$this->is_locked = flock($this->file, LOCK_EX | LOCK_NB);

		return $this->is_locked;
	}

	public function unlock(): bool
	{
		if (!static::is_locked())
		{
			return false;
		}


		$unlocked = flock($this->file, LOCK_UN);

		$this->is_locked = !$unlocked;

		return $unlocked;
	}

	public function is_locked(): bool
	{
		return $this->is_locked;
	}

	/**
	 * Unlocks and closes the file handle if open, then removes the lock file from disk
	 */
	public function delete(): bool
	{
		if ($this->file)
		{
			if (static::is_locked() and !static::unlock()) { return false; }
			if (static::is_opened() and !static::close())  { return false; }

			$this->file = null;
		}

		if ($this->path and file_exists($this->path))
		{
			return unlink($this->path);
		}

		return false;
	}
}
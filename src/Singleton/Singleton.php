<?php

namespace Stackstra\Singleton;

use Stackstra\Filesystem\FileObject;

class Singleton
{
	private FileObject $file;

	public function __construct($path)
	{
		$this->file = new FileObject($path);
	}

	public function lock()
	{
		if (!$this->file->isExist())
		{
			if (!$this->file->create())
			{
				return false;
			}
		}

		$this->file->openIfClosed('r');

		return $this->file->lockForRead(non_blocking: true);
	}

	public function unlock()
	{
		$this->file->unlock();
	}

	public function __destruct()
	{
		static::unlock();

		$this->file->close();
	}
}

<?php

namespace Stackstra\Filesystem;


use const Stackstra\MASK_0;
use const Stackstra\MASK_1;
use const Stackstra\MASK_2;
use const Stackstra\MASK_3;
use const Stackstra\MASK_20;

use function Stackstra\is_nullptr;

use Stackstra\Cache\Cache;
use Stackstra\Etc\Nullptr;
use Stackstra\Exceptions\Exceptions;
use Stackstra\Types\Strings;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Search
{
	const int TYPE_NONE = MASK_0;
	const int TYPE_FILE = MASK_1;
	const int TYPE_LINK = MASK_2;
	const int TYPE_DIR  = MASK_3;

	const int TYPE_UNKNOWN = MASK_20;

	const int TYPE_ANY = self::TYPE_FILE | self::TYPE_DIR | self::TYPE_LINK | self::TYPE_UNKNOWN;

	const int SORT_NONE    = MASK_1;
	const int SORT_NATURAL = MASK_2;

	const int ORDER_NONE = MASK_0;
	const int ORDER_ASC  = MASK_1;
	const int ORDER_DESC = MASK_2;

	const string DEFAULT_PATTERN            = '*';
	const    int DEFAULT_TYPES              = self::TYPE_ANY;
	const    int DEFAULT_SORT               = self::SORT_NATURAL;
	const    int DEFAULT_ORDER              = self::ORDER_ASC;
	const   bool DEFAULT_IS_RECURSIVE       = false;
	const   bool DEFAULT_IS_OUTPUT_RELATIVE = false;

	const string SETTING_PATH               = 'path';
	const string SETTING_PATTERN            = 'pattern';
	const string SETTING_TYPES              = 'types';
	const string SETTING_SORT               = 'sort';
	const string SETTING_ORDER              = 'order';
	const string SETTING_IS_RECURSIVE       = 'is_recursive';
	//const string SETTING_IS_OUTPUT_RELATIVE = 'is_output_relative';

	protected Cache $settings;

	public function __construct(string $path)
	{
		$this->settings = new Cache();

		$this
			->path     ($path)
			->pattern  (self::DEFAULT_PATTERN)
			->types    (self::DEFAULT_TYPES)
			->sort     (self::DEFAULT_SORT)
			->order    (self::DEFAULT_ORDER)
			->recursive(self::DEFAULT_IS_RECURSIVE);
			//->outputRelative(self::DEFAULT_IS_OUTPUT_RELATIVE);
	}

	public static function make(string $path): static
	{
		return new static(path: $path);
	}

	protected function auto(string $key, mixed $value)
	{
		if (is_nullptr($value))
		{
			return $this->get($key);
		}

		return $this->set($key, $value);
	}

	protected function set(string $key, mixed $val): self
	{
		$this->settings->set($key, $val);

		return $this;
	}

	protected function get(string $key): mixed
	{
		return $this->settings->get($key);
	}

	public function path(Nullptr|string $value = NULLPTR)
	{
		return $this->auto(self::SETTING_PATH, $value);
	}

	public function pattern(Nullptr|string $value = NULLPTR)
	{
		return $this->auto(self::SETTING_PATTERN, $value);
	}

	public function types(Nullptr|int $value = NULLPTR)
	{
		return $this->auto(self::SETTING_TYPES, $value);
	}

	public function typeAny():       self { return $this->types(static::TYPE_ANY);  }
	public function typeFile():      self { return $this->types(static::TYPE_FILE); }
	public function typeLink():      self { return $this->types(static::TYPE_LINK); }
	public function typeDirectory(): self { return $this->types(static::TYPE_DIR);  }

	public function isType(int $type): bool
	{
		return $this->types() & $type;
	}

	public function isTypeAny():       bool { return $this->isType(self::TYPE_ANY);  }
	public function isTypeFile():      bool { return $this->isType(self::TYPE_FILE); }
	public function isTypeLink():      bool { return $this->isType(self::TYPE_LINK); }
	public function isTypeDirectory(): bool { return $this->isType(self::TYPE_DIR);  }

	public function sort(Nullptr|int $value = NULLPTR)
	{
		return $this->auto(self::SETTING_SORT, $value);
	}

	public function sortNatural()
	{
		return $this->sort(self::SORT_NATURAL);
	}

	public function order(Nullptr|int $value = NULLPTR)
	{
		return $this->auto(self::SETTING_ORDER, $value);
	}

	public function orderDesc()
	{
		return $this->order(self::ORDER_DESC);
	}

	public function orderAsc()
	{
		return $this->order(self::ORDER_ASC);
	}

	public function recursive(Nullptr|bool $value = NULLPTR)
	{
		return $this->auto(self::SETTING_IS_RECURSIVE, $value);
	}

	/**
	 * @return array<string, int> path => type
	 */
	protected function query(): array
	{
		$types = $this->types();
		$path = $this->path();
		$path = Strings::trimRight($path, DIRECTORY_SEPARATOR);

		Directory::assertExists($path);


		$results = [];

		if ($this->recursive())
		{
			$files = [];

			$directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);

			foreach (new RecursiveIteratorIterator($directory) as $file_info)
			{
				/** @var SplFileInfo $file_info */

				if (fnmatch($this->pattern(), $file_info->getFilename()))
				{
					$files[] = $file_info->getPathname();
				}
			}
		}
		else
		{
			$files = glob(Directory::pathCombine($path, $this->pattern()), GLOB_NOSORT);
		}

		foreach ($files as $file_path)
		{
			     if (is_file($file_path)) { $type = self::TYPE_FILE; }
			else if (is_link($file_path)) { $type = self::TYPE_LINK; }
			else if  (is_dir($file_path)) { $type = self::TYPE_DIR;  }
			else
			{
				Exceptions::warning("unable to recognize the file type: `$file_path`");

				$type = self::TYPE_UNKNOWN;
			}

			$match_file      = ($types & self::TYPE_FILE) && ($type & self::TYPE_FILE);
			$match_link      = ($types & self::TYPE_LINK) && ($type & self::TYPE_LINK);
			$match_directory = ($types & self::TYPE_DIR)  && ($type & self::TYPE_DIR);

			if ($match_file or $match_link or $match_directory)
			{
				$results[$file_path] = $type;
			}
		}

		switch ($this->sort())
		{
			case self::SORT_NONE:
				# do nothing
				break;

			case self::SORT_NATURAL:
				ksort($results, flags: SORT_NATURAL);

				switch ($this->order())
				{
					case self::ORDER_DESC:
						$results = array_reverse($results, preserve_keys: true);
						break;
				}
				break;

			default:
				Exceptions::warning("unexpected sort type: `{$this->sort()}`");
		}

		return $results;
	}

	/**
	 * @return DirectoryObject[]|FileObject[]
	 */
	public function find(): array
	{
		$files = self::query();

		$result = [];

		foreach ($files as $path => $type)
		{
			     if ($type & self::TYPE_DIR)  { $result[] = DirectoryObject::make($path, format: false); }
			else if ($type & self::TYPE_FILE) { $result[] =      FileObject::make($path); }
			else
			{
				Exceptions::warning("unsupported file type: `$type`");
			}
		}

		return $result;
	}
}

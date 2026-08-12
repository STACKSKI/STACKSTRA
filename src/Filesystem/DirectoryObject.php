<?php

namespace Stackstra\Filesystem;

use Stackstra\DateTime\DateTime;
use Stackstra\Etc\Reflection;
use Stackstra\Exceptions\Exceptions;
use Stackstra\Filesystem\Traits\CanIterate;
use Stackstra\Filesystem\Traits\CanSearch;
use Stackstra\Filesystem\Traits\HasPath;
use Stackstra\Types\Items;
use Stackstra\Types\Strings;
use Iterator;

/**
 * @property-read self $DATE
 * @property-read self $DATE_TIME
 * @property-read self $MOST_RECENT_DIR
 *
 * @return self[]
 */
class DirectoryObject implements Iterator
{
	use HasPath;
	use CanSearch;
	use CanIterate;

	const string MARKER_DIR              = '{DIR}';
	const string MARKER_DATE             = '{DATE}';
	const string MARKER_DATE_TIME        = '{DATE_TIME}';
	const string MARKER_MOST_RECENT_DIR  = '{MOST_RECENT_DIR}';
	const string MARKER_MOST_RECENT_FILE = '{MOST_RECENT_FILE}';

	public function __construct(string $path)
	{
		$this->path = $path;
	}

	public function __get(string $name): self
	{
		$markers = self::markers();

		if (isset($markers[$name]))
		{
			$name = $markers[$name];

			$format = true;
		}
		else
		{
			$format = false;
		}

		return self::make(path: [$this->path(), $name], format: $format);
	}

	public static function make(string|array $path, bool|array $format = false): self
	{
		if (is_array($path))
		{
			$path = Directory::pathCombine($path);
		}

		if ($format)
		{
			$path = self::format($path, is_array($format) ? $format : []);
		}

		return new self(path: $path);
	}

	public static function markers(): array
	{
		static $markers;

		if ($markers === null)
		{
			$vals = Reflection::getConstantsPublicStartWith(self::class, 'MARKER_');
			$keys = array_map(fn($marker) => Strings::trim($marker, ['{', '}']), $vals);

			$markers = array_combine($keys, $vals);
		}

		return $markers;
	}

	public function file(string $file_name, ?string $mode = null): FileObject
	{
		return FileObject::make($this->path() . DIRECTORY_SEPARATOR . $file_name, mode: $mode);
	}

	public function fileDateTime(?string $mode = null, string $extension = '', string $format = DateTime::FORMAT_YMD_HIS): FileObject
	{
		$file_name = DateTime::make()->format($format) . $extension;

		return self::file($file_name, mode: $mode);
	}

	public function fileMostRecent(?string $mode = null): FileObject
	{
		$path = self::format($this->path() . DIRECTORY_SEPARATOR . self::MARKER_MOST_RECENT_FILE);

		return FileObject::make($path, mode: $mode);
	}

	/**
	 * @return self[]|FileObject[]
	 */
	public function all(): array
	{
		return $this->search()->find();
	}

	/**
	 * @return self[]
	 */
	public function directories(): array
	{
		return $this->search()->typeDirectory()->find();
	}

	/**
	 * @return FileObject[]
	 */
	public function files(string $pattern = Search::DEFAULT_PATTERN): array
	{
		return $this->search()->typeFile()->pattern($pattern)->find();
	}

	public function up(int $levels = 1): self
	{
		$path = $this->path();

		$parent = dirname($path, levels: $levels);

		if ($parent === $path)
		{
			Exceptions::error("unable to find a parent directory for the following path: $path");
		}

		return self::make($parent, format: false);
	}

	public function down(array|string $path, bool|array $format = false): self
	{
		return self::make(path: [$this->path(), ... (array) $path], format: $format);
	}

	public function create(bool $recursive = true): bool
	{
		if (!Directory::create(path: $this->path, recursive: $recursive))
		{
			Exceptions::error("unable to create the following directory: $this->path");
		}

		return true;
	}

	public function createIfNotExist(bool $recursive = true): self
	{
		if (!$this->isExist())
		{
			$this->create(recursive: $recursive);
		}

		return $this;
	}

	public function isExist(bool $cached = true): bool
	{
		return Directory::isExist($this->path());
	}

	public function delete(bool $recursively = false)
	{
		return Directory::remove($this->path(), $recursively);
	}

	public function move(string|array|self $to): self
	{
		if (is_string($to) or is_array($to))
		{
			$to = self::make($to);
		}

		$to->createIfNotExist();

		$from = $this;

		if (!Directory::move($from->path(), $to->path()))
		{
			Exceptions::error("unable to move the directory from `{$from->path()}` to `{$to->path()}`");
		}

		return $to;
	}

	public static function format(string $path, array $variables = []): string
	{
		foreach ($variables as $name => $value)
		{
			$path = Strings::replace($path, '{' . Strings::toUppercase($name) . '}', $value);
		}

		$path = Strings::replace($path, self::MARKER_DIR,       $path);
		$path = Strings::replace($path, self::MARKER_DATE,      DateTime::make()->formatYmd());
		$path = Strings::replace($path, self::MARKER_DATE_TIME, DateTime::make()->formatYmdHis());


		$count = preg_match_all('/{[A-Z_]+}/', $path, $matches);

		if (!$count)
		{
			return $path;
		}

		foreach ($matches[0] as $match)
		{
			$type = match ($match)
			{
				self::MARKER_MOST_RECENT_DIR  => Search::TYPE_DIR,
				self::MARKER_MOST_RECENT_FILE => Search::TYPE_FILE,

				default => Exceptions::error("unexpected marker: `$match`"),
			};

			$path_parts = explode($match, $path, limit: 2);

			$files = Search::make(path: $path_parts[0])->types($type)->orderDesc()->find();

			if (!$files)
			{
				Exceptions::error("unable to find required files in the following directory: $path_parts[0]");
			}

			/** @var FileObject|DirectoryObject $file */
			$file = Items::first($files);

			$file_name = File::name($file->path());

			$path = implode($file_name, $path_parts);
		}

		return $path;
	}
}

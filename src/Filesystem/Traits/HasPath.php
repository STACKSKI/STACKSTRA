<?php

namespace Stackstra\Filesystem\Traits;

use Stackstra\Filesystem\File;

trait HasPath
{
	///**
	// * @var bool|null false = doesn't exist
	// *                true  = exists
	// *                null  = unknown (not checked)
	// */
	//protected ?bool $exist = null;

	protected string $path;

	public function path(): string
	{
		return $this->path;
	}

	public function directory(): string
	{
		return File::directory($this->path);
	}

	public function extension(): string
	{
		return File::extension($this->path);
	}

	public function name(bool $include_extension = true): string
	{
		return File::name($this->path, $include_extension);
	}

	public function size(): ?int
	{
		return File::size($this->path());
	}

	abstract public function isExist(/*bool $cached = true*/): bool;

	abstract public function create(): bool;
}

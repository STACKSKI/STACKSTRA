<?php

namespace Stackstra\Filesystem\Traits;

use Stackstra\Filesystem\Search;

trait CanSearch
{
	public function search(): Search
	{
		return Search::make($this->path());
	}

	abstract public function path(): string;
}

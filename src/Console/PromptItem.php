<?php

namespace Stackstra\Console;

use Stackstra\Cache\Cache;
use function Stackstra\is_nullptr;

use Closure;

use Stackstra\Etc\Nullptr;

class PromptItem
{
	const string SETTING_LABEL    = 'label';
	const string SETTING_VALUE    = 'value';
	const string SETTING_CALLBACK = 'callback';

	//protected string  $label;
	//protected mixed   $value;
	//protected Closure $callback;

	public function __construct(string $label, mixed $value, Closure $callback)
	{
		//$this->cache = new Cache();
		//
		//$this->label();
		//
		//$this->label    = $label;
		//$this->value    = $value;
		//$this->callback = $callback;
	}

	public static function instance(string $label, mixed $value, Closure $callback)
	{

	}

	//public function auto(Nullptr|string $label = NULLPTR): string|self
	//{
	//	if (is_nullptr($label))
	//	{
	//		return $this->label;
	//	}
	//
	//	$this->label = $label;
	//
	//	return $this;
	//}

	//protected function auto(string $key, mixed $value)
	//{
	//	if (is_nullptr($value))
	//	{
	//		return $this->get($key);
	//	}
	//
	//	return $this->set($key, $value);
	//}
}

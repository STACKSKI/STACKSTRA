<?php

namespace Stackstra\Curl;

use function Stackstra\is_nullptr;

class CurlOptions
{
	const string OPTION_PROGRESS             = 'progress';
	const string OPTION_THREADS              = 'threads';
	const string OPTION_MAX_ATTEMPTS         = 'max_attempts';
	const string OPTION_THROTTLE_SLOTS       = 'throttle_slots';
	const string OPTION_THROTTLE_INTERVAL    = 'throttle_interval';
	const string OPTION_REMEMBER_RESPONSES   = 'remember_responses';

	const array OPTIONS_DEFAULT =
	[
		self::OPTION_PROGRESS           => false,
		self::OPTION_THREADS            => 1,
		self::OPTION_MAX_ATTEMPTS       => 1,
		self::OPTION_THROTTLE_SLOTS     => null,
		self::OPTION_THROTTLE_INTERVAL  => null,
		self::OPTION_REMEMBER_RESPONSES => true,
	];

	protected array $options = [];

	public function __construct(array $options = [])
	{
		$this->options = $options + self::OPTIONS_DEFAULT;
	}

	public function set(array $options): self
	{
		$this->options = $options + $this->options;

		return $this;
	}

	protected function option(string $option, mixed $value = NULLPTR): mixed
	{
		if (!is_nullptr($value))
		{
			$this->options[$option] = $value;
		}

		return $this->options[$option];
	}

	public function progress          ($value = NULLPTR) { return $this->option(self::OPTION_PROGRESS,           $value); }
	public function threads           ($value = NULLPTR) { return $this->option(self::OPTION_THREADS,            $value); }
	public function max_attempts      ($value = NULLPTR) { return $this->option(self::OPTION_MAX_ATTEMPTS,       $value); }
	public function throttle_slots    ($value = NULLPTR) { return $this->option(self::OPTION_THROTTLE_SLOTS,     $value); }
	public function throttle_interval ($value = NULLPTR) { return $this->option(self::OPTION_THROTTLE_INTERVAL,  $value); }
	public function remember_responses($value = NULLPTR) { return $this->option(self::OPTION_REMEMBER_RESPONSES, $value); }
}

<?php

namespace Stackstra\Curl;

class CurlEventArguments
{
	public function __construct
	(
		public ?Curl             $curl          = null,
		public ?CurlTask         $task          = null,
		public ?CurlResponse     $response      = null,
		public ?CurlEvents       $events        = null,
		public ?CurlResponseList $response_list = null,
	)
	{
	}
}

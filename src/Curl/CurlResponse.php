<?php

namespace Stackstra\Curl;

use function Stackstra\dd;
use function Stackstra\get;

use CurlHandle;

use Stackstra\Types\Boolean;
use Stackstra\Types\Chars;
use Stackstra\Exceptions\Exceptions;

class CurlResponse
{
	const array CONSTANTS =
	[
		'CURLINFO_CONNECT_TIME',
		'CURLINFO_CONTENT_LENGTH_DOWNLOAD',
		'CURLINFO_CONTENT_LENGTH_UPLOAD',
		'CURLINFO_CONTENT_TYPE',
		'CURLINFO_EFFECTIVE_URL',
		'CURLINFO_FILETIME',
		'CURLINFO_HEADER_OUT',
		'CURLINFO_HEADER_SIZE',
		'CURLINFO_HTTP_CODE',
		'CURLINFO_LASTONE',
		'CURLINFO_NAMELOOKUP_TIME',
		'CURLINFO_PRETRANSFER_TIME',
		'CURLINFO_PRIVATE',
		'CURLINFO_REDIRECT_COUNT',
		'CURLINFO_REDIRECT_TIME',
		'CURLINFO_REQUEST_SIZE',
		'CURLINFO_SIZE_DOWNLOAD',
		'CURLINFO_SIZE_UPLOAD',
		'CURLINFO_SPEED_DOWNLOAD',
		'CURLINFO_SPEED_UPLOAD',
		'CURLINFO_SSL_VERIFYRESULT',
		'CURLINFO_STARTTRANSFER_TIME',
		'CURLINFO_TOTAL_TIME',
		'CURLINFO_HTTP_CONNECTCODE',
		'CURLINFO_HTTPAUTH_AVAIL',
		'CURLINFO_RESPONSE_CODE',
		'CURLINFO_PROXYAUTH_AVAIL',
		'CURLINFO_OS_ERRNO',
		'CURLINFO_NUM_CONNECTS',
		'CURLINFO_SSL_ENGINES',
		'CURLINFO_COOKIELIST',
		'CURLINFO_FTP_ENTRY_PATH',
		'CURLINFO_REDIRECT_URL',
		'CURLINFO_APPCONNECT_TIME',
		'CURLINFO_PRIMARY_IP',
		'CURLINFO_CERTINFO',
		'CURLINFO_CONDITION_UNMET',
		'CURLINFO_RTSP_CLIENT_CSEQ',
		'CURLINFO_RTSP_CSEQ_RECV',
		'CURLINFO_RTSP_SERVER_CSEQ',
		'CURLINFO_RTSP_SESSION_ID',
		'CURLINFO_LOCAL_IP',
		'CURLINFO_LOCAL_PORT',
		'CURLINFO_PRIMARY_PORT',
		'CURLINFO_HTTP_VERSION',
		'CURLINFO_PROTOCOL',
		'CURLINFO_PROXY_SSL_VERIFYRESULT',
		'CURLINFO_SCHEME',
		'CURLINFO_CONTENT_LENGTH_DOWNLOAD_T',
		'CURLINFO_CONTENT_LENGTH_UPLOAD_T',
		'CURLINFO_SIZE_DOWNLOAD_T',
		'CURLINFO_SIZE_UPLOAD_T',
		'CURLINFO_SPEED_DOWNLOAD_T',
		'CURLINFO_SPEED_UPLOAD_T',
		'CURLINFO_FILETIME_T',
		'CURLINFO_APPCONNECT_TIME_T',
		'CURLINFO_CONNECT_TIME_T',
		'CURLINFO_NAMELOOKUP_TIME_T',
		'CURLINFO_PRETRANSFER_TIME_T',
		'CURLINFO_REDIRECT_TIME_T',
		'CURLINFO_STARTTRANSFER_TIME_T',
		'CURLINFO_TOTAL_TIME_T',
		'CURLINFO_CAINFO',
		'CURLINFO_CAPATH',
		'CURLINFO_CONN_ID',
		'CURLINFO_EFFECTIVE_METHOD',
		'CURLINFO_HTTPAUTH_USED',
		'CURLINFO_POSTTRANSFER_TIME_T',
		'CURLINFO_PROXYAUTH_USED',
		'CURLINFO_PROXY_ERROR',
		'CURLINFO_QUEUE_TIME_T',
		'CURLINFO_REFERER',
		'CURLINFO_RETRY_AFTER',
		'CURLINFO_USED_PROXY'
	];

	public string $id;
    public string $url;
    public array $info;
    public mixed $content;
	public array $contentJSON = [];
    public int $http_code;
    public int $attempts;

	public CurlTask $task;

	/**
	 * CurlResponse constructor.
	 *
	 * @param $task CurlTask
	 */
	public function __construct(CurlTask $task)
	{
		$this->id  = $task->id;
		$this->url = $task->url;

		$curl_handle = $task->getHandle();

		$this->content   = curl_multi_getcontent($curl_handle);
		$this->info      = static::info($curl_handle);
		$this->http_code = (int) get($this->info, 'CURLINFO_HTTP_CODE');
		$this->attempts  = $task->connection_attempts;

		$this->task = $task;
	}

	/**
	 * @param $curl_handle CurlHandle|resource
	 *
	 * @return array
	 */
	private static function info($curl_handle)
	{
		$info = [];

		foreach (self::constants() as $constant)
		{
			$info[$constant] = curl_getinfo($curl_handle, constant($constant));
		}

		return $info;
	}

	public static function constants()
	{
		static $constants;

		if ($constants === null)
		{
			foreach (self::CONSTANTS as $constant)
			{
				if (defined($constant))
				{
					$constants[] = $constant;
				}
			}
		}

		return $constants;
	}

	public function isConnectionEstablished(): bool
	{
		return (bool) $this->http_code;
	}

	public function isHttpCode($code): bool
	{
		return ((int) $code) === ((int) $this->http_code);
	}

	public function isHttpCodeOK(): bool
	{
		return ((int) Chars::first($this->http_code) === 2) && $this->http_code >= 200 && $this->http_code < 300;
	}

	public function isContentJSON(): bool
	{
		return is_string($this->content) && str_starts_with(ltrim($this->content), '{');
	}

	public function decodeJSON(?bool $associative = null, int $depth = 512, int $flags = 0): mixed
	{
		return json_decode($this->content, associative: $associative, depth: $depth, flags: $flags);
	}

	public function assertOK(): self
	{
		if (!$this->isHttpCodeOK())
		{
			Exceptions::error("HTTP code is `$this->http_code`. URL: $this->url");
		}

		return $this;
	}

	public function assertJSON(): self
	{
		if (!self::isContentJSON())
		{
			Exceptions::error('The content is not a valid JSON');
		}

		return $this;
	}

	public function dd(): never
	{
		dd($this);
	}
}

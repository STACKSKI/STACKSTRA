<?php

namespace Stackstra\Etc;

use Stackstra\Types\Strings;
use Stackstra\URL\URL;

use function Stackstra\get;

class Headers
{
	const CODE_SUCCESS               = 200;
	const CODE_TEMPORARY_REDIRECT    = 307;
	const CODE_BAD_REQUEST           = 400;
	const CODE_UNAUTHORIZED          = 401;
	const CODE_FORBIDDEN             = 403;
	const CODE_NOT_FOUND             = 404;
	const CODE_INTERNAL_SERVER_ERROR = 500;

	const CODES =
	[
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',

		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',

		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended'
	];

	const METHOD_GET     = 'GET';
	const METHOD_HEAD    = 'HEAD';
	const METHOD_POST    = 'POST';
	const METHOD_PUT     = 'PUT';
	const METHOD_DELETE  = 'DELETE';
	const METHOD_CONNECT = 'CONNECT';
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_TRACE   = 'TRACE';
	const METHOD_PATCH   = 'PATCH';

	public static function code($code)
	{
		$protocol = get($_SERVER, 'SERVER_PROTOCOL', 'HTTP/1.0');

		header("$protocol $code " . self::CODES[$code], true, $code);
	}

	# see also http://wiki.nginx.org/XSendfile
	public static function file(string $file_path, string|bool $file_name = false, string $file_type = 'application/octet-stream', bool $die = true)
	{
		if ($file_name === false)
		{
			$file_name = basename($file_path);
		}

		if (file_exists($file_path))
		{
			@set_time_limit(0);

			header('Content-Description: File Transfer');
			header('Content-Type: ' . $file_type);
			header('Content-Disposition: attachment; filename="' . str_replace('"', "'", $file_name) . '"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file_path));

			readfile($file_path);
		}

		if ($die)
		{
			exit;
		}
	}

	public static function json(mixed $data, int $flags = 0)
	{
		if (APP_IS_LOCALHOST or get('_pretty_json'))
		{
			$flags |= JSON_PRETTY_PRINT;
		}

		header('Content-Type: application/json');

		echo json_encode($data, $flags);

		die;
	}

	public static function success()
	{
		header('HTTP/1.0 200 OK');
	}

	public static function badRequest()
	{
		header('HTTP/1.0 400 Bad Request');
	}

	/**
	 * Send an HTTP 404 header (not found)
	 */
	public static function notFound()
	{
		header('HTTP/1.0 404 Not Found');
	}

	/**
	 * Send an HTTP 403 header (forbidden)
	 */
	public static function forbidden()
	{
		header('HTTP/1.0 403 Forbidden');
	}

	/**
	 * Send an HTTP 410 header (gone)
	 */
	public static function gone()
	{
		header('HTTP/1.1 410 Gone');
	}

	/**
	 * Send an HTTP 418 header (I'm a teapot)
	 */
	public static function teapot()
	{
		header('HTTP/1.1 418 I\'m a teapot');
	}

	public static function unavailable($retry_after = 120)
	{
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: ' . (int) $retry_after);

		exit;
	}

	public static function redirect($url, $replace = null, $code = null)
	{
		header("Location: $url", $replace, $code);

		exit;
	}

	public static function reload($url = null)
	{
		if ($url === null)
		{
			$url = URL::current();
		}

		static::redirect($url, true, 303);
	}

	public static function referer()
	{
		if (isset($_SERVER["HTTP_REFERER"]))
		{
			return $_SERVER["HTTP_REFERER"];
		}

		return null;
	}

	protected static function isRequestType($type): bool
	{
		return get($_SERVER, 'REQUEST_METHOD') === $type;
	}

	public static function isRequestPost(): bool { return self::isRequestType('POST'); }
	public static function isRequestGet():  bool { return self::isRequestType('GET');  }

	public static function sent(): bool
	{
		return headers_sent();
	}

	public static function acceptLanguages()
	{
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			return [];
		}

		$string = $_SERVER['HTTP_ACCEPT_LANGUAGE'];


		$langs = explode(',', $string);


		$result = [];

		foreach ($langs as $lang)
		{
			if (Strings::contains($lang, ';'))
			{
				[$country, $priority] = explode(';', $lang);

				$result[$country] = $priority;
			}
		}

		return $result;
	}
}
<?php

namespace Stackstra\URL;

use Stackstra\Etc\Headers;

use function Stackstra\get;

class URL
{
	protected string $url;

	protected ?array $parts;

	public function __construct(?string $url = null)
	{
		static::set($url);
	}

	public function set(?string $url = null): void
	{
		     if ($url) { $this->url = $url; }
		else           { $this->url = static::current(); }

		$this->parts = static::parse($this->url);
	}

	public function part($name, $value = null)
	{
		if ($value !== null)
		{
			$this->parts[$name] = $value;

			$this->url = static::unparse($this->parts);
		}

		return get($this->parts, $name);
	}
	                                                                                     # https://login:password@rude.com:8080/path?key=val#anchor
	public function scheme  ($value = null) { return static::part('scheme',   $value); } # https
	public function user    ($value = null) { return static::part('user',     $value); } #         login
	public function pass    ($value = null) { return static::part('pass',     $value); } #               password
	public function host    ($value = null) { return static::part('host',     $value); } #                        rude.com
	public function port    ($value = null) { return static::part('port',     $value); } #                                 8080
	public function path    ($value = null) { return static::part('path',     $value); } #                                     /path
	public function query   ($value = null) { return static::part('query',    $value); } #                                           key=val
	public function fragment($value = null) { return static::part('fragment', $value); } #                                                   anchor
	public function domain()                                                             # https://rude.com
	{
		if (static::host())
		{
			if (static::scheme())
			{
				return static::scheme() . '://' . static::host();
			}

			return static::host();
		}

		return null;
	}

	public function get($params = [], $erase_query = false)
	{
		if (!$params)
		{
			return $this->url;
		}


		if (is_string($params))
		{
			$params = static::queryToArray($params);
		}


		$parts = $this->parts;

		     if ($erase_query) { $query = ''; }
		else                   { $query = static::query(); }

		$query = static::queryToArray($query);

		foreach ($params as $key => $val)
		{
			$query[$key] = $val;
		}


		//# hotfix: ajax reload appends extra parameters to the url
		//unset($query['_ajax']);


		$query = static::arrayToQuery($query);

		$parts['query'] = $query;

		return static::unparse($parts);
	}

	protected static function arrayToQuery($array)
	{
		return http_build_query($array);
	}

	protected static function queryToArray($string)
	{
		parse_str($string, $result);

		return $result;
	}


	/**
	 * Parse a URL and return its components
	 *
	 * @param string $url
	 * @param int    $component
	 *
	 * @return \stdClass|null
	 */
	public static function parse($url = null, $component = -1)
	{
		if ($url === null)
		{
			$url = static::current();
		}


		$result = parse_url($url, $component);

		if ($result === false)
		{
			return null;
		}

		return $result;
	}

	public static function parseScheme  ($url = null) { return static::parse($url, PHP_URL_SCHEME);   } #
	public static function parseUser    ($url = null) { return static::parse($url, PHP_URL_USER);     }
	public static function parsePass    ($url = null) { return static::parse($url, PHP_URL_PASS);     }
	public static function parseHost    ($url = null) { return static::parse($url, PHP_URL_HOST);     }
	public static function parsePort    ($url = null) { return static::parse($url, PHP_URL_PORT);     }
	public static function parsePath    ($url = null) { return static::parse($url, PHP_URL_PATH);     }
	public static function parseQuery   ($url = null) { return static::parse($url, PHP_URL_QUERY);    }
	public static function parseFragment($url = null) { return static::parse($url, PHP_URL_FRAGMENT); }

	/**
	 * Unparse components and return its url (alias for http_build_url(), but you don't need pecl_http library for it)
	 *
	 * @param $parsed_url
	 *
	 * @return string
	 */
	public static function unparse($parsed_url)
	{
		$scheme   = isset($parsed_url['scheme'])   ?       $parsed_url['scheme'] . '://' : '';
		$host     = isset($parsed_url['host'])     ?       $parsed_url['host']           : '';
		$port     = isset($parsed_url['port'])     ? ':' . $parsed_url['port']           : '';
		$user     = isset($parsed_url['user'])     ?       $parsed_url['user']           : '';
		$pass     = isset($parsed_url['pass'])     ? ':' . $parsed_url['pass']           : '';
		$pass     = ($user or $pass)               ? "$pass@"                            : '';
		$path     = isset($parsed_url['path'])     ?       $parsed_url['path']           : '';
		$query    = isset($parsed_url['query'])    ? '?' . $parsed_url['query']          : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment']       : '';

		return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
	}

	public static function reload()
	{
		$url = static::current();

		static::redirect($url);
	}

	public static function current($skip_protocol = false, $skip_domain = false)
	{
		if ($skip_domain !== false)
		{
			return $_SERVER['REQUEST_URI'];
		}


		$url = '';

		if ($skip_protocol === false)
		{
			$protocol = 'http';

			if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on')
			{
				$protocol .= 's';
			}

			$url = $protocol . '://';
		}

		if (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] != '80')
		{
			$url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		}
		else
		{
			$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		return $url;
	}

	/**
	 * URL encoder
	 *
	 * @param string $string The string to be encoded
	 *
	 * $result = url::encode('http://site.com'); # string(23) "http%3A%2F%2Fsite.com"
	 *
	 * @return string
	 */
	public static function encode($string)
	{
		return urlencode($string);
	}

	/**
	 * URL decoder
	 *
	 * @param string $string The url to be decoded
	 *
	 * $result = url::decode('http%3A%2F%2Fsite.com'); # string(15) "http://site.com"
	 *
	 * @return string
	 */
	public static function decode($string)
	{
		return urldecode($string);
	}

	public static function redirect($url)
	{
		Headers::redirect($url);
	}

	//public function param($key, $val)
	//{
	//	if (!$this->query)
	//	{
	//
	//	}
	//
	//	return static::params($url, [$key => $val]);
	//}
	//
	//public static function param_remove($url, $key)
	//{
	//	return static::param($url, $key, null);
	//}

	//public static function params($url, $params)
	//{
	//	if (!$params)
	//	{
	//		return $url;
	//	}
	//
	//
	//	$parts = static::parse($url);
	//
	//	if (!isset($parts->path))
	//	{
	//		$parts->path = '';
	//	}
	//
	//	if (!strings::ends_with($parts->path, '/'))
	//	{
	//		$parts->path .= '/';
	//	}
	//
	//	if (!isset($parts->query))
	//	{
	//		$parts->query = '';
	//	}
	//
	//
	//	parse_str($parts->query, $params_old);
	//
	//	foreach ($params as $key => $val)
	//	{
	//		$params_old[$key] = $val;
	//	}
	//
	//	$parts->query = http_build_query($params_old);
	//
	//	return url::unparse($parts);
	//}

	//public static function timestamp($url, $file_path = null)
	//{
	//	if ($file_path === null)
	//	{
	//		$file_path = File::pathCombine(RUDE_DIR_APP, $url);
	//	}
	//
	//	if (!filesystem::is_exist($file_path))
	//	{
	//		return $url;
	//	}
	//
	//
	//	$url = new URL($url);
	//
	//	return $url->get(['t' => filesystem::timestamp($file_path)]);
	//}
}
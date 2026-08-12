<?php

namespace Stackstra\Curl;

use Stackstra\Exceptions\Exceptions;
use Stackstra\Types\Strings;

use CurlHandle;

class CurlTask
{
	/**
	 * @var CurlHandle|resource
	 */
	private $handle;

	public $id;
	public $id_internal;

	public $url;

	public int|float|null $connection_timestamp = null;
	public int            $connection_attempts  = 0;

	public array $settings;


	/** @var CurlResponse */
	public $response = null;

	public function __construct($url, $id = null, array $settings = [], $defaults = true)
	{
		$this->url = $url;

		     if ($id === null) { $this->id = $this->url; }
		else                   { $this->id = $id;        }

		$this->settings = $settings;

		$this->handle = curl_init($this->url);

		if ($this->handle === false)
		{
			Exceptions::warning('curl_init has not been initialized');
		}


		####################
		# default settings #
		####################

		if ($defaults)
		{
			static::setTimeoutExecution();
			static::setTimeoutConnection();
			static::setEncoding();
			static::enableRedirects();
			static::enableReturnTransfer();
			static::disablePost();
			static::disableSslVerification();
		}
	}

	public function __destruct()
	{
		static::close();
	}

	public function close()
	{
		# curl_close() has had no effect since PHP 8.0 (handles are freed by refcounting) and is deprecated since 8.5

		$this->handle = null;
	}

	public function & getHandle() { return $this->handle; }

	public function set($key, $val)
	{
		return curl_setopt($this->handle, $key, $val);
	}

	public function setTimeoutExecution ($timeout = 30)  { return static::set(CURLOPT_TIMEOUT,        $timeout);    }
	public function setTimeoutConnection($timeout = 30)  { return static::set(CURLOPT_CONNECTTIMEOUT, $timeout);    }
	public function setEncoding         ($encoding = '') { return static::set(CURLOPT_ENCODING,       $encoding);   }
	public function setUserAgent        ($user_agent)    { return static::set(CURLOPT_USERAGENT,      $user_agent); }
	public function setCookieFileSave   ($file_path)     { return static::set(CURLOPT_COOKIEJAR,      $file_path);  }
	public function setCookieFileLoad   ($file_path)     { return static::set(CURLOPT_COOKIEFILE,     $file_path);  }
	public function setReferer          ($referer)       { return static::set(CURLOPT_REFERER,        $referer);    }
	public function setMaxRedirects     ($max = -1)       { return static::set(CURLOPT_MAXREDIRS,      $max);        } # -1: unlimited
	public function setInterface        ($interface)      { return static::set(CURLOPT_INTERFACE,      $interface);  }

	public function setCookie($cookie)
	{
		if (is_array($cookie))
		{
			$cookie = implode('; ', $cookie);
		}

		return static::set(CURLOPT_COOKIE, $cookie);
	}

	public function setHeader($header)
	{
		if (is_array($header))
		{
			$header = implode(PHP_EOL, $header);
		}

		if (is_string($header))
		{
			$header = [$header];
		}

		return static::set(CURLOPT_HTTPHEADER, $header);
	}

	public function setPost($post_fields)
	{
		if (is_array($post_fields))
		{
			$post_fields = http_build_query($post_fields);
		}

		return static::enablePost() and static::set(CURLOPT_POSTFIELDS, $post_fields);
	}

	public function setJson($post_fields)
	{
		$json = json_encode($post_fields);

		return static::enablePost() && static::set(CURLOPT_POSTFIELDS, $json) and
		                               static::set(CURLOPT_HTTPHEADER,
		                               [
		                                   'Content-Type: application/json',
		                                   'Content-Length: ' . Strings::size($json)
		                               ]);
	}

	public function setPut($data = '')
	{
		$file = tmpfile();

		fwrite($file, $data);

		fseek($file, 0);

		return static::enablePut() and static::set(CURLOPT_INFILE, $file) and static::set(CURLOPT_INFILESIZE, Strings::size($data));
	}

	/**
	 * Multipart/form-data POST; unlike setPost(), $fields may contain CURLFile instances for file uploads
	 *
	 * @param array $fields
	 */
	public function setMultipart(array $fields)
	{
		return static::enablePost() and static::set(CURLOPT_POSTFIELDS, $fields);
	}

	public function setAuth($login, $password)
	{
		return static::set(CURLOPT_USERPWD, "$login:$password");
	}

	public function setUsername($username) { return static::set(CURLOPT_USERNAME, $username); }
	public function setPassword($password) { return static::set(CURLOPT_PASSWORD, $password); }

	/**
	 * @param int $auth one or more CURLAUTH_* flags bitwise-OR'd together (default: let curl pick the best supported method)
	 */
	public function setHttpAuth(int $auth = CURLAUTH_ANY)
	{
		return static::set(CURLOPT_HTTPAUTH, $auth);
	}

	/**
	 * OAuth 2.0 bearer token; also switches the auth method to CURLAUTH_BEARER, as curl requires
	 */
	public function setBearerToken(string $token)
	{
		return static::setHttpAuth(CURLAUTH_BEARER) and static::set(CURLOPT_XOAUTH2_BEARER, $token);
	}

	public function setCaInfo($file_path) { return static::set(CURLOPT_CAINFO, $file_path); }
	public function setCaPath($dir_path)  { return static::set(CURLOPT_CAPATH, $dir_path);  }

	public function setRange($range) { return static::set(CURLOPT_RANGE, $range); }
	public function setPort(int $port) { return static::set(CURLOPT_PORT, $port); }

	public function enableFailOnError()  { return static::set(CURLOPT_FAILONERROR, true);  }
	public function disableFailOnError() { return static::set(CURLOPT_FAILONERROR, false); }

	/**
	 * @param callable $callback fn(CurlHandle $handle, string $data): int — return strlen($data) to keep receiving, anything else aborts the transfer
	 */
	public function onWrite(callable $callback)
	{
		return static::set(CURLOPT_WRITEFUNCTION, $callback);
	}

	/**
	 * @param callable $callback fn(CurlHandle $handle, string $header_line): int — return strlen($header_line) to keep receiving, anything else aborts the transfer
	 */
	public function onHeader(callable $callback)
	{
		return static::set(CURLOPT_HEADERFUNCTION, $callback);
	}

	/**
	 * @param callable $callback fn(CurlHandle $handle, int $download_total, int $downloaded, int $upload_total, int $uploaded): int — return non-zero to abort the transfer
	 */
	public function onProgress(callable $callback)
	{
		return static::set(CURLOPT_NOPROGRESS, false) and static::set(CURLOPT_XFERINFOFUNCTION, $callback);
	}

	/**
	 * Override DNS resolution for specific host:port pairs
	 *
	 * @param string[] $resolve entries formatted as "host:port:address" (or "-host:port" to remove an earlier entry)
	 */
	public function setResolve(array $resolve)
	{
		return static::set(CURLOPT_RESOLVE, $resolve);
	}

	public function setUnixSocketPath($path) { return static::set(CURLOPT_UNIX_SOCKET_PATH, $path); }

	public function setMaxFileSize(int $bytes) { return static::set(CURLOPT_MAXFILESIZE, $bytes); }

	/**
	 * @param int|null $receive bytes/second cap on download speed (CURLOPT_MAX_RECV_SPEED_LARGE)
	 * @param int|null $send    bytes/second cap on upload speed (CURLOPT_MAX_SEND_SPEED_LARGE)
	 */
	public function setMaxSpeed(?int $receive = null, ?int $send = null)
	{
		$result = true;

		if ($receive !== null) { $result = $result and static::set(CURLOPT_MAX_RECV_SPEED_LARGE, $receive); }
		if ($send !== null)    { $result = $result and static::set(CURLOPT_MAX_SEND_SPEED_LARGE, $send);    }

		return $result;
	}

	public function setProxyHttp($proxy)
	{
		return static::set(CURLOPT_PROXYTYPE, CURLPROXY_HTTP) && static::set(CURLOPT_PROXY, $proxy);
	}

	public function setProxySocks4($proxy)
	{
		return static::set(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4) && static::set(CURLOPT_PROXY, $proxy);
	}

	public function setProxySocks5($proxy)
	{
		return static::set(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5) && static::set(CURLOPT_PROXY, $proxy);
	}

	public function setFile($handler)
	{
		return static::set(CURLOPT_FILE, $handler);
	}

	public function setHttpVersion($version = CURL_HTTP_VERSION_NONE) { return static::set(CURLOPT_HTTP_VERSION, $version); }

	public function useHttp1  () { return static::setHttpVersion(CURL_HTTP_VERSION_1_1); }
	public function useHttp2  () { return static::setHttpVersion(CURL_HTTP_VERSION_2_0); }

	public function useHttp3()
	{
		if (!defined('CURL_HTTP_VERSION_3'))
		{
			return Exceptions::warning('CURL_HTTP_VERSION_3 is not available (curl built without HTTP/3 support)');
		}

		return static::setHttpVersion(CURL_HTTP_VERSION_3);
	}

	/**
	 * @param bool     $enable
	 * @param int|null $idle     seconds of inactivity before sending a keepalive probe (CURLOPT_TCP_KEEPIDLE)
	 * @param int|null $interval seconds between keepalive probes (CURLOPT_TCP_KEEPINTVL)
	 */
	public function setTcpKeepAlive(bool $enable = true, ?int $idle = null, ?int $interval = null)
	{
		$result = static::set(CURLOPT_TCP_KEEPALIVE, $enable);

		if ($idle !== null)     { $result = $result and static::set(CURLOPT_TCP_KEEPIDLE,  $idle);     }
		if ($interval !== null) { $result = $result and static::set(CURLOPT_TCP_KEEPINTVL, $interval); }

		return $result;
	}

	/**
	 * Abort the transfer if the transfer speed drops below $bytes_per_second for $seconds
	 */
	public function setLowSpeedLimit(int $bytes_per_second, int $seconds)
	{
		return static::set(CURLOPT_LOW_SPEED_LIMIT, $bytes_per_second) and static::set(CURLOPT_LOW_SPEED_TIME, $seconds);
	}

	public function setUrl($url)
	{
		return static::set(CURLOPT_URL, $url);
	}

	public function enableDelete()              { return static::set(CURLOPT_CUSTOMREQUEST, 'DELETE'); } # DELETE
	public function enablePatch()               { return static::set(CURLOPT_CUSTOMREQUEST, 'PATCH');  } # PATCH
	public function enablePut()                 { return static::set(CURLOPT_PUT,            true);    } # PUT
	public function enablePost()                { return static::set(CURLOPT_POST,           true);    } # POST
	public function enableGet()                 { return static::set(CURLOPT_HTTPGET,        true);    } # GET
	public function enableHead()                { return static::set(CURLOPT_NOBODY,         true);    } # HEAD
	public function enableRedirects()           { return static::set(CURLOPT_FOLLOWLOCATION, true);    }
	public function enableReturnHeaders()       { return static::set(CURLOPT_HEADER,         true);    }
	public function enableReturnTransfer()      { return static::set(CURLOPT_RETURNTRANSFER, true);    }
	public function enableVerbose()             { return static::set(CURLOPT_VERBOSE,        true);    }
	public function enableSslVerification()     { return static::set(CURLOPT_SSL_VERIFYPEER, true);    }
	public function enableSslVerificationHost() { return static::set(CURLOPT_SSL_VERIFYHOST, 2);       } # 2: check that the cert's name matches the host

	public function disableDelete()             { return static::set(CURLOPT_CUSTOMREQUEST,  null);    } # DELETE
	public function disablePatch()              { return static::set(CURLOPT_CUSTOMREQUEST,  null);    } # PATCH
	public function disablePut()                { return static::set(CURLOPT_PUT,            false);   } # PUT
	public function disablePost()               { return static::set(CURLOPT_POST,           false);   } # POST
	public function disableHead()               { return static::set(CURLOPT_NOBODY,         false);   } # HEAD
	public function disableRedirects()          { return static::set(CURLOPT_FOLLOWLOCATION, false);   }
	public function disableReturnHeaders()      { return static::set(CURLOPT_HEADER,         false);   }
	public function disableReturnTransfer()     { return static::set(CURLOPT_RETURNTRANSFER, false);   }
	public function disableVerbose()            { return static::set(CURLOPT_VERBOSE,        false);   }
	public function disableSslVerification()    { return static::set(CURLOPT_SSL_VERIFYPEER, false);   }
	public function disableSslVerificationHost() { return static::set(CURLOPT_SSL_VERIFYHOST, 0);       }
}

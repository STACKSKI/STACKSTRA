<?php

namespace Stackstra\Tests\Curl;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\CurlTask;
use Stackstra\Tests\TestCase;

/**
 * All URLs used here are loopback placeholders (http://127.0.0.1/...) that curl_init()/curl_setopt()
 * merely record — nothing in this test ever calls curl_exec()/curl_multi_exec(), so no request is made.
 */
#[CoversClass(CurlTask::class)]
class CurlTaskTest extends TestCase
{
    private const URL = 'http://127.0.0.1/placeholder';

    public function testConstruct(): void
    {
        // id omitted: defaults to the url itself
        $task = new CurlTask(self::URL, defaults: false);
        $this->assertSame(self::URL, $task->url);
        $this->assertSame(self::URL, $task->id);
        $this->assertSame([], $task->settings);

        // explicit id
        $task = new CurlTask(self::URL, 'my-id', defaults: false);
        $this->assertSame('my-id', $task->id);

        // explicit settings
        $task = new CurlTask(self::URL, settings: ['a' => 1], defaults: false);
        $this->assertSame(['a' => 1], $task->settings);

        // defaults=true (default): the handle exists and every default setopt call succeeds
        $task = new CurlTask(self::URL);
        $this->assertNotNull($task->getHandle());
    }

    public function testClose(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $task->close();

        $this->assertNull($task->getHandle());

        // closing twice is a no-op, not an error
        $task->close();
        $this->assertNull($task->getHandle());
    }

    public function testGetHandle(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertInstanceOf(\CurlHandle::class, $task->getHandle());
    }

    public function testSet(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->set(CURLOPT_TIMEOUT, 5));
    }

    public function testSetTimeoutExecution(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setTimeoutExecution(10));
    }

    public function testSetTimeoutConnection(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setTimeoutConnection(10));
    }

    public function testSetEncoding(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setEncoding('gzip'));
    }

    public function testSetUserAgent(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setUserAgent('MyAgent/1.0'));
    }

    public function testSetCookieFileSave(): void
    {
        $task = new CurlTask(self::URL, defaults: false);
        $path = tempnam(sys_get_temp_dir(), 'cookie_');

        $this->assertTrue($task->setCookieFileSave($path));

        unlink($path);
    }

    public function testSetCookieFileLoad(): void
    {
        $task = new CurlTask(self::URL, defaults: false);
        $path = tempnam(sys_get_temp_dir(), 'cookie_');

        $this->assertTrue($task->setCookieFileLoad($path));

        unlink($path);
    }

    public function testSetReferer(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setReferer('http://127.0.0.1/referer'));
    }

    public function testSetMaxRedirects(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setMaxRedirects(5));

        // default: unlimited
        $this->assertTrue($task->setMaxRedirects());
    }

    public function testSetInterface(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setInterface('eth0'));
    }

    public function testSetCookie(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        // raw string
        $this->assertTrue($task->setCookie('a=1; b=2'));

        // array of "name=value" pairs, imploded with "; "
        $task = new CurlTask(self::URL, defaults: false);
        $this->assertTrue($task->setCookie(['a=1', 'b=2']));
    }

    public function testSetHeader(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        // string header
        $this->assertTrue($task->setHeader('X-Test: 1'));

        // array of headers
        $this->assertTrue($task->setHeader(['X-A: 1', 'X-B: 2']));
    }

    public function testSetPost(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        // array: converted via http_build_query()
        $this->assertTrue($task->setPost(['a' => 1]));

        // raw string
        $task = new CurlTask(self::URL, defaults: false);
        $this->assertTrue($task->setPost('a=1'));
    }

    public function testSetJson(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setJson(['a' => 1]));
    }

    public function testSetPut(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setPut('body content'));
    }

    public function testSetMultipart(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setMultipart(['field' => 'value']));
    }

    public function testSetAuth(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setAuth('user', 'pass'));
    }

    public function testSetUsername(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setUsername('user'));
    }

    public function testSetPassword(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setPassword('pass'));
    }

    public function testSetHttpAuth(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        // default: CURLAUTH_ANY
        $this->assertTrue($task->setHttpAuth());

        // explicit flag
        $this->assertTrue($task->setHttpAuth(CURLAUTH_BASIC));
    }

    public function testSetBearerToken(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setBearerToken('token123'));
    }

    public function testSetProxyHttp(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setProxyHttp('127.0.0.1:8080'));
    }

    public function testSetProxySocks4(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setProxySocks4('127.0.0.1:1080'));
    }

    public function testSetProxySocks5(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setProxySocks5('127.0.0.1:1080'));
    }

    public function testSetFile(): void
    {
        $task = new CurlTask(self::URL, defaults: false);
        $handle = tmpfile();

        $this->assertTrue($task->setFile($handle));
    }

    public function testSetCaInfo(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setCaInfo('/etc/ssl/certs/ca-certificates.crt'));
    }

    public function testSetCaPath(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setCaPath('/etc/ssl/certs'));
    }

    public function testSetRange(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setRange('0-499'));
    }

    public function testSetPort(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setPort(8080));
    }

    public function testEnableFailOnError(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableFailOnError());
    }

    public function testDisableFailOnError(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableFailOnError());
    }

    public function testOnWrite(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->onWrite(fn ($handle, $data) => strlen($data)));
    }

    public function testOnHeader(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->onHeader(fn ($handle, $data) => strlen($data)));
    }

    public function testOnProgress(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->onProgress(fn (...$args) => 0));
    }

    public function testSetResolve(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setResolve(['example.com:80:127.0.0.1']));
    }

    public function testSetUnixSocketPath(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setUnixSocketPath('/tmp/does-not-exist.sock'));
    }

    public function testSetMaxFileSize(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->setMaxFileSize(1048576));
    }

    public function testSetMaxSpeed(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        // both directions
        $this->assertTrue($task->setMaxSpeed(1024, 2048));

        // receive only
        $task = new CurlTask(self::URL, defaults: false);
        $this->assertTrue($task->setMaxSpeed(1024));

        // send only
        $task = new CurlTask(self::URL, defaults: false);
        $this->assertTrue($task->setMaxSpeed(null, 2048));

        // neither: no-op, still returns true
        $task = new CurlTask(self::URL, defaults: false);
        $this->assertTrue($task->setMaxSpeed());
    }

    public function testSetHttpVersion(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        // explicit version
        $this->assertTrue($task->setHttpVersion(CURL_HTTP_VERSION_1_1));

        // default: CURL_HTTP_VERSION_NONE (let curl decide)
        $this->assertTrue($task->setHttpVersion());
    }

    public function testUseHttp1(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->useHttp1());
    }

    public function testUseHttp2(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->useHttp2());
    }

    public function testUseHttp3(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        if (!defined('CURL_HTTP_VERSION_3'))
        {
            // unsupported curl build: warns instead of setting an undefined constant
            $this->assertTrue($this->silently(fn () => $task->useHttp3()));

            return;
        }

        $this->assertTrue($task->useHttp3());
    }

    public function testSetTcpKeepAlive(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        // enable only, idle/interval omitted
        $this->assertTrue($task->setTcpKeepAlive());

        // disable, with idle/interval provided too
        $task = new CurlTask(self::URL, defaults: false);
        $this->assertTrue($task->setTcpKeepAlive(false, 30, 15));
    }

    public function testSetLowSpeedLimit(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setLowSpeedLimit(1024, 10));
    }

    public function testSetUrl(): void
    {
        $task = new CurlTask(self::URL, defaults: false);

        $this->assertTrue($task->setUrl('http://127.0.0.1/other'));
    }

    public function testEnableDelete(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableDelete());
    }

    public function testEnablePatch(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enablePatch());
    }

    public function testEnablePut(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enablePut());
    }

    public function testEnablePost(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enablePost());
    }

    public function testEnableGet(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableGet());
    }

    public function testEnableHead(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableHead());
    }

    public function testEnableRedirects(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableRedirects());
    }

    public function testEnableReturnHeaders(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableReturnHeaders());
    }

    public function testEnableReturnTransfer(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableReturnTransfer());
    }

    public function testEnableVerbose(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableVerbose());
    }

    public function testEnableSslVerification(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableSslVerification());
    }

    public function testEnableSslVerificationHost(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->enableSslVerificationHost());
    }

    public function testDisableDelete(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableDelete());
    }

    public function testDisablePatch(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disablePatch());
    }

    public function testDisablePut(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disablePut());
    }

    public function testDisablePost(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disablePost());
    }

    public function testDisableHead(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableHead());
    }

    public function testDisableRedirects(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableRedirects());
    }

    public function testDisableReturnHeaders(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableReturnHeaders());
    }

    public function testDisableReturnTransfer(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableReturnTransfer());
    }

    public function testDisableVerbose(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableVerbose());
    }

    public function testDisableSslVerification(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableSslVerification());
    }

    public function testDisableSslVerificationHost(): void
    {
        $this->assertTrue((new CurlTask(self::URL, defaults: false))->disableSslVerificationHost());
    }
}

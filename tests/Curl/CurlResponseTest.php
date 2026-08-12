<?php

namespace Stackstra\Tests\Curl;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\CurlResponse;
use Stackstra\Curl\CurlTask;
use Stackstra\Tests\TestCase;

/**
 * CurlTask handles here are never added to a curl_multi handle and never executed, so no connection is ever
 * attempted — curl_multi_getcontent()/curl_getinfo() simply read the (empty/default) state of an unexecuted
 * handle. All URLs are loopback placeholders (http://127.0.0.1/...) that curl_init() merely records.
 */
#[CoversClass(CurlResponse::class)]
class CurlResponseTest extends TestCase
{
    private const URL = 'http://127.0.0.1/placeholder';

    private function task(): CurlTask
    {
        return new CurlTask(self::URL, defaults: false);
    }

    public function testConstruct(): void
    {
        $task = $this->task();
        $task->connection_attempts = 3;

        $response = new CurlResponse($task);

        $this->assertSame(self::URL, $response->id);
        $this->assertSame(self::URL, $response->url);
        $this->assertSame($task, $response->task);
        $this->assertSame(3, $response->attempts);

        // never executed: no content and a 0 http code
        $this->assertNull($response->content);
        $this->assertSame(0, $response->http_code);

        // the info array is populated from every defined CURLINFO_* constant
        $this->assertArrayHasKey('CURLINFO_HTTP_CODE', $response->info);
    }

    public function testConstants(): void
    {
        $constants = CurlResponse::constants();

        $this->assertContains('CURLINFO_HTTP_CODE', $constants);

        // every returned name is one of the class's declared candidates, and actually defined
        foreach ($constants as $constant)
        {
            $this->assertContains($constant, CurlResponse::CONSTANTS);
            $this->assertTrue(defined($constant));
        }

        // cached: repeated calls return the same list
        $this->assertSame($constants, CurlResponse::constants());
    }

    public function testIsConnectionEstablished(): void
    {
        $response = new CurlResponse($this->task());

        // never executed: http_code is 0
        $this->assertFalse($response->isConnectionEstablished());

        $response->http_code = 200;
        $this->assertTrue($response->isConnectionEstablished());
    }

    public function testIsHttpCode(): void
    {
        $response = new CurlResponse($this->task());
        $response->http_code = 404;

        $this->assertTrue($response->isHttpCode(404));

        // loose (int) comparison: a numeric string also matches
        $this->assertTrue($response->isHttpCode('404'));

        $this->assertFalse($response->isHttpCode(200));
    }

    public function testIsHttpCodeOK(): void
    {
        $response = new CurlResponse($this->task());

        foreach ([200, 204, 299] as $code)
        {
            $response->http_code = $code;
            $this->assertTrue($response->isHttpCodeOK(), "expected $code to be OK");
        }

        foreach ([0, 199, 300, 404, 500] as $code)
        {
            $response->http_code = $code;
            $this->assertFalse($response->isHttpCodeOK(), "expected $code to not be OK");
        }
    }

    public function testIsContentJSON(): void
    {
        $response = new CurlResponse($this->task());

        $response->content = '{"a":1}';
        $this->assertTrue($response->isContentJSON());

        // leading whitespace is trimmed before the check
        $response->content = "  \n{\"a\":1}";
        $this->assertTrue($response->isContentJSON());

        // a JSON array (starts with "[") is not detected as JSON content
        $response->content = '[1,2,3]';
        $this->assertFalse($response->isContentJSON());

        $response->content = 'not json';
        $this->assertFalse($response->isContentJSON());

        // non-string content: false
        $response->content = null;
        $this->assertFalse($response->isContentJSON());
    }

    public function testDecodeJSON(): void
    {
        $response = new CurlResponse($this->task());
        $response->content = '{"a":1}';

        // associative omitted (null): objects
        $decoded = $response->decodeJSON();
        $this->assertEquals((object) ['a' => 1], $decoded);

        // associative=true: arrays
        $this->assertSame(['a' => 1], $response->decodeJSON(true));
    }

    public function testAssertOK(): void
    {
        $response = new CurlResponse($this->task());
        $response->http_code = 200;

        $this->assertSame($response, $response->assertOK());

        $response->http_code = 404;

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/404/');

        $this->silently(fn () => $response->assertOK());
    }

    public function testAssertJSON(): void
    {
        $response = new CurlResponse($this->task());
        $response->content = '{"a":1}';

        $this->assertSame($response, $response->assertJSON());

        $response->content = 'not json';

        $this->expectException(Exception::class);

        $this->silently(fn () => $response->assertJSON());
    }

    /**
     * dd() delegates to the global dd() helper, which var_dump()s its argument and then exit()s —
     * untestable in-process. Shell out and confirm the dump and the clean exit both happened.
     */
    public function testDd(): void
    {
        $autoload = __DIR__ . '/../../vendor/autoload.php';

        $code = 'require ' . var_export($autoload, true) . ';'
              . '$task = new \Stackstra\Curl\CurlTask(' . var_export(self::URL, true) . ', defaults: false);'
              . '(new \Stackstra\Curl\CurlResponse($task))->dd();'
              . 'fwrite(STDOUT, "UNREACHABLE");';

        $output = shell_exec('php -r ' . escapeshellarg($code) . '; echo "|exit:$?"');

        $this->assertStringContainsString('object(Stackstra\Curl\CurlResponse)', $output);
        $this->assertStringNotContainsString('UNREACHABLE', $output);
        $this->assertStringEndsWith('|exit:1', trim($output));
    }
}

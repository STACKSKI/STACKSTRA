<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\Headers;
use Stackstra\Tests\TestCase;

#[CoversClass(Headers::class)]
class HeadersTest extends TestCase
{
    private string $autoload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->autoload = __DIR__ . '/../../vendor/autoload.php';
    }

    private function runPhp(string $code): string
    {
        $bootstrap = 'require ' . var_export($this->autoload, true) . ';';

        return trim(shell_exec('php -r ' . escapeshellarg($bootstrap . $code) . '; echo "|exit:$?"'));
    }

    public function testCode(): void
    {
        // header() is a no-op under CLI; just confirm it doesn't throw
        Headers::code(404);

        $this->assertTrue(true);
    }

    public function testFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'headers_test_');
        file_put_contents($path, 'hello world');

        // die=false: headers are sent (no-op under CLI) and the file content is streamed out
        ob_start();
        Headers::file($path, die: false);
        $output = ob_get_clean();

        $this->assertSame('hello world', $output);

        // missing file: no output, no error, and die=false means execution continues
        unlink($path);
        ob_start();
        Headers::file($path, die: false);
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testJson(): void
    {
        // json() reads the APP_IS_LOCALHOST constant, which this library expects the
        // consuming application to define; define it here since nothing else does
        $output = $this->runPhp('define("APP_IS_LOCALHOST", false); \Stackstra\Etc\Headers::json(["a" => 1]);');

        $this->assertStringStartsWith(json_encode(['a' => 1]), $output);
        $this->assertStringEndsWith('|exit:0', $output);

        // APP_IS_LOCALHOST=true (or _pretty_json set) enables JSON_PRETTY_PRINT
        $output = $this->runPhp('define("APP_IS_LOCALHOST", true); \Stackstra\Etc\Headers::json(["a" => 1]);');

        $this->assertStringStartsWith(json_encode(['a' => 1], JSON_PRETTY_PRINT), $output);
    }

    public function testSuccess(): void
    {
        Headers::success();

        $this->assertTrue(true);
    }

    public function testBadRequest(): void
    {
        Headers::badRequest();

        $this->assertTrue(true);
    }

    public function testNotFound(): void
    {
        Headers::notFound();

        $this->assertTrue(true);
    }

    public function testForbidden(): void
    {
        Headers::forbidden();

        $this->assertTrue(true);
    }

    public function testGone(): void
    {
        Headers::gone();

        $this->assertTrue(true);
    }

    public function testTeapot(): void
    {
        Headers::teapot();

        $this->assertTrue(true);
    }

    public function testUnavailable(): void
    {
        // exits, so shell out; default retry_after
        $output = $this->runPhp('\Stackstra\Etc\Headers::unavailable(); echo "UNREACHABLE";');

        $this->assertSame('|exit:0', $output);

        // explicit retry_after
        $output = $this->runPhp('\Stackstra\Etc\Headers::unavailable(30); echo "UNREACHABLE";');

        $this->assertSame('|exit:0', $output);
    }

    public function testRedirect(): void
    {
        $output = $this->runPhp('\Stackstra\Etc\Headers::redirect("https://example.com"); echo "UNREACHABLE";');

        $this->assertSame('|exit:0', $output);
    }

    public function testReload(): void
    {
        // url omitted: falls back to URL::current(), built from $_SERVER
        $output = $this->runPhp(
            '$_SERVER["HTTP_HOST"] = "example.com"; $_SERVER["REQUEST_URI"] = "/x"; $_SERVER["SERVER_PORT"] = "80";'
            . '\Stackstra\Etc\Headers::reload(); echo "UNREACHABLE";'
        );

        $this->assertSame('|exit:0', $output);

        // explicit url
        $output = $this->runPhp('\Stackstra\Etc\Headers::reload("https://explicit.example"); echo "UNREACHABLE";');

        $this->assertSame('|exit:0', $output);
    }

    public function testReferer(): void
    {
        unset($_SERVER['HTTP_REFERER']);
        $this->assertNull(Headers::referer());

        $_SERVER['HTTP_REFERER'] = 'https://from.example';
        $this->assertSame('https://from.example', Headers::referer());
    }

    public function testIsRequestPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(Headers::isRequestPost());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertFalse(Headers::isRequestPost());
    }

    public function testIsRequestGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Headers::isRequestGet());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertFalse(Headers::isRequestGet());
    }

    public function testSent(): void
    {
        $this->assertSame(headers_sent(), Headers::sent());
    }

    public function testAcceptLanguages(): void
    {
        // header not present: empty array
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $this->assertSame([], Headers::acceptLanguages());

        // entries without a ";q=" priority are silently dropped
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,fr;q=0.8,de;q=0.6';
        $this->assertSame(['fr' => 'q=0.8', 'de' => 'q=0.6'], Headers::acceptLanguages());
    }
}

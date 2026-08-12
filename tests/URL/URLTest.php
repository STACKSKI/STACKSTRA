<?php

namespace Stackstra\Tests\URL;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\URL\URL;

#[CoversClass(URL::class)]
class URLTest extends TestCase
{
    private const string FULL = 'https://login:password@rude.com:8080/path?key=val#anchor';

    public function testConstruct(): void
    {
        // explicit url: stored and parsed as-is
        $url = new URL(self::FULL);
        $this->assertSame(self::FULL, $url->get());

        // url omitted: falls back to current(), built from $_SERVER
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/from-server';
        $_SERVER['SERVER_PORT'] = '80';
        unset($_SERVER['HTTPS']);

        $url = new URL();
        $this->assertSame('http://example.com/from-server', $url->get());
    }

    public function testSet(): void
    {
        $url = new URL(self::FULL);

        $url->set('https://other.com/');
        $this->assertSame('https://other.com/', $url->get());

        // omitted argument falls back to current()
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/reset';
        $_SERVER['SERVER_PORT'] = '80';
        unset($_SERVER['HTTPS']);

        $url->set();
        $this->assertSame('http://example.com/reset', $url->get());
    }

    public function testPart(): void
    {
        $url = new URL(self::FULL);

        // getter: value omitted -> returns the current part
        $this->assertSame('rude.com', $url->part('host'));

        // part not present in the url: null
        $bare = new URL('https://rude.com');
        $this->assertNull($bare->part('port'));

        // setter: value provided -> updates the part and rebuilds the stored url
        $url->part('host', 'changed.com');
        $this->assertSame('changed.com', $url->part('host'));
        $this->assertStringContainsString('changed.com', $url->get());
    }

    public function testScheme(): void
    {
        $url = new URL(self::FULL);

        $this->assertSame('https', $url->scheme());

        $url->scheme('ftp');
        $this->assertSame('ftp', $url->scheme());
    }

    public function testUser(): void
    {
        $url = new URL(self::FULL);

        $this->assertSame('login', $url->user());

        $url->user('other');
        $this->assertSame('other', $url->user());
    }

    public function testPass(): void
    {
        $url = new URL(self::FULL);

        $this->assertSame('password', $url->pass());

        $url->pass('other');
        $this->assertSame('other', $url->pass());
    }

    public function testHost(): void
    {
        $url = new URL(self::FULL);

        $this->assertSame('rude.com', $url->host());

        $url->host('other.com');
        $this->assertSame('other.com', $url->host());
    }

    public function testPort(): void
    {
        $url = new URL(self::FULL);

        $this->assertSame(8080, $url->port());

        $url->port(9090);
        $this->assertSame(9090, $url->port());
    }

    public function testPath(): void
    {
        $url = new URL(self::FULL);

        $this->assertSame('/path', $url->path());

        $url->path('/other');
        $this->assertSame('/other', $url->path());
    }

    public function testQuery(): void
    {
        $url = new URL(self::FULL);

        $this->assertSame('key=val', $url->query());

        $url->query('a=b');
        $this->assertSame('a=b', $url->query());
    }

    public function testFragment(): void
    {
        $url = new URL(self::FULL);

        $this->assertSame('anchor', $url->fragment());

        $url->fragment('other');
        $this->assertSame('other', $url->fragment());
    }

    public function testDomain(): void
    {
        // scheme + host present
        $url = new URL(self::FULL);
        $this->assertSame('https://rude.com', $url->domain());

        // host present, no scheme (protocol-relative)
        $url = new URL('//rude.com/path');
        $this->assertSame('rude.com', $url->domain());

        // no host at all
        $url = new URL('/just/a/path');
        $this->assertNull($url->domain());
    }

    public function testGet(): void
    {
        $url = new URL(self::FULL);

        // no params: the raw stored url
        $this->assertSame(self::FULL, $url->get());

        // string params, merged into the existing query
        $result = $url->get('foo=bar');
        $this->assertStringContainsString('key=val', $result);
        $this->assertStringContainsString('foo=bar', $result);

        // array params
        $result = $url->get(['foo' => 'bar']);
        $this->assertStringContainsString('foo=bar', $result);

        // erase_query=true drops the existing query before merging in the new params
        $result = $url->get(['foo' => 'bar'], true);
        $this->assertStringNotContainsString('key=val', $result);
        $this->assertStringContainsString('foo=bar', $result);
    }

    public function testParse(): void
    {
        $result = URL::parse(self::FULL);

        $this->assertSame('rude.com', $result['host']);
        $this->assertSame(8080, $result['port']);

        // invalid url: null
        $this->assertNull(URL::parse('http:///bad_url::'));

        // explicit component argument returns just that piece
        $this->assertSame('rude.com', URL::parse(self::FULL, PHP_URL_HOST));

        // url omitted: falls back to current()
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/x';
        $_SERVER['SERVER_PORT'] = '80';
        unset($_SERVER['HTTPS']);

        $this->assertSame('example.com', URL::parse(null, PHP_URL_HOST));
    }

    public function testParseScheme(): void
    {
        $this->assertSame('https', URL::parseScheme(self::FULL));
    }

    public function testParseUser(): void
    {
        $this->assertSame('login', URL::parseUser(self::FULL));
    }

    public function testParsePass(): void
    {
        $this->assertSame('password', URL::parsePass(self::FULL));
    }

    public function testParseHost(): void
    {
        $this->assertSame('rude.com', URL::parseHost(self::FULL));
    }

    public function testParsePort(): void
    {
        $this->assertSame(8080, URL::parsePort(self::FULL));
    }

    public function testParsePath(): void
    {
        $this->assertSame('/path', URL::parsePath(self::FULL));
    }

    public function testParseQuery(): void
    {
        $this->assertSame('key=val', URL::parseQuery(self::FULL));
    }

    public function testParseFragment(): void
    {
        $this->assertSame('anchor', URL::parseFragment(self::FULL));
    }

    public function testUnparse(): void
    {
        $parts = [
            'scheme'   => 'https',
            'user'     => 'login',
            'pass'     => 'password',
            'host'     => 'rude.com',
            'port'     => 8080,
            'path'     => '/path',
            'query'    => 'key=val',
            'fragment' => 'anchor',
        ];

        $this->assertSame(self::FULL, URL::unparse($parts));

        // missing pieces are simply omitted, not left as gaps
        $this->assertSame('https://rude.com/path', URL::unparse(['scheme' => 'https', 'host' => 'rude.com', 'path' => '/path']));

        // user without pass: no ":" but the "@" is kept
        $this->assertSame('https://login@rude.com', URL::unparse(['scheme' => 'https', 'user' => 'login', 'host' => 'rude.com']));

        // completely empty parts: empty string
        $this->assertSame('', URL::unparse([]));
    }

    public function testCurrent(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/path?x=1';
        $_SERVER['SERVER_PORT'] = '80';
        unset($_SERVER['HTTPS']);

        // default: protocol + host + path, port 80 omitted
        $this->assertSame('http://example.com/path?x=1', URL::current());

        // HTTPS on: scheme becomes https
        $_SERVER['HTTPS'] = 'on';
        $this->assertSame('https://example.com/path?x=1', URL::current());
        unset($_SERVER['HTTPS']);

        // non-standard port: included in the host portion
        $_SERVER['SERVER_PORT'] = '8080';
        $this->assertSame('http://example.com:8080/path?x=1', URL::current());
        $_SERVER['SERVER_PORT'] = '80';

        // skip_protocol=true: no scheme prefix
        $this->assertSame('example.com/path?x=1', URL::current(true));

        // skip_domain not false (any non-false value): returns the raw REQUEST_URI
        $this->assertSame('/path?x=1', URL::current(false, true));
    }

    public function testEncode(): void
    {
        $this->assertSame('http%3A%2F%2Fsite.com', URL::encode('http://site.com'));
        $this->assertSame('a+b', URL::encode('a b'));
    }

    public function testDecode(): void
    {
        $this->assertSame('http://site.com', URL::decode('http%3A%2F%2Fsite.com'));
        $this->assertSame('a b', URL::decode('a+b'));
    }

    /**
     * redirect() delegates to Headers::redirect(), which calls header() and then exit()s —
     * untestable in-process (and header() is a CLI no-op, so headers_list() can't confirm it
     * either). Shell out and confirm the process terminates cleanly right after the call,
     * proving exit() ran and nothing after it (in redirect() or reload()) executed.
     */
    public function testRedirect(): void
    {
        $autoload = __DIR__ . '/../../vendor/autoload.php';

        $code = 'require ' . var_export($autoload, true) . ';'
              . '\Stackstra\URL\URL::redirect("https://redirected.example");'
              . 'fwrite(STDOUT, "UNREACHABLE");';

        $output = shell_exec('php -r ' . escapeshellarg($code) . '; echo "exit:$?"');

        $this->assertSame('exit:0', trim($output));
    }

    public function testReload(): void
    {
        $autoload = __DIR__ . '/../../vendor/autoload.php';

        $code = 'require ' . var_export($autoload, true) . ';'
              . '$_SERVER["HTTP_HOST"] = "example.com"; $_SERVER["REQUEST_URI"] = "/here"; $_SERVER["SERVER_PORT"] = "80";'
              . '\Stackstra\URL\URL::reload();'
              . 'fwrite(STDOUT, "UNREACHABLE");';

        $output = shell_exec('php -r ' . escapeshellarg($code) . '; echo "exit:$?"');

        $this->assertSame('exit:0', trim($output));
    }
}

<?php

namespace Stackstra\Tests\Curl;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\CurlResponseList;
use Stackstra\Curl\CurlTask;
use Stackstra\Curl\CurlResponse;
use Stackstra\Tests\TestCase;

#[CoversClass(CurlResponseList::class)]
class CurlResponseListTest extends TestCase
{
    private function response(string $url = 'http://127.0.0.1/placeholder'): CurlResponse
    {
        return new CurlResponse(new CurlTask($url, defaults: false));
    }

    public function testConstruct(): void
    {
        // no argument: empty
        $list = new CurlResponseList();
        $this->assertSame([], $list->get());

        // seeded via constructor
        $response = $this->response();
        $list = new CurlResponseList([$response]);
        $this->assertSame([$response], $list->get());
    }

    public function testAdd(): void
    {
        $list = new CurlResponseList();
        $response = $this->response();

        $list->add($response);

        $this->assertSame([$response], $list->get());
    }

    public function testMerge(): void
    {
        $a = $this->response('http://127.0.0.1/a');
        $b = $this->response('http://127.0.0.1/b');

        $list1 = new CurlResponseList([$a]);
        $list2 = new CurlResponseList([$b]);

        $list1->merge($list2);

        $this->assertSame([$a, $b], $list1->get());
    }

    public function testGet(): void
    {
        $response = $this->response();
        $list = new CurlResponseList([$response]);

        $this->assertSame([$response], $list->get());
    }

    public function testFirst(): void
    {
        // empty list: null
        $this->assertNull((new CurlResponseList())->first());

        $a = $this->response('http://127.0.0.1/a');
        $b = $this->response('http://127.0.0.1/b');
        $list = new CurlResponseList([$a, $b]);

        $this->assertSame($a, $list->first());
    }

    public function testReset(): void
    {
        $list = new CurlResponseList([$this->response()]);

        $list->reset();

        $this->assertSame([], $list->get());
    }

    public function testHasErrors(): void
    {
        // no responses at all: no errors
        $this->assertFalse((new CurlResponseList())->hasErrors());

        // a response whose connection failed (http_code 0): counts as an error
        $list = new CurlResponseList([$this->response()]);
        $this->assertTrue($list->hasErrors());
    }

    public function testMap(): void
    {
        $a = $this->response('http://127.0.0.1/a');
        $b = $this->response('http://127.0.0.1/b');
        $list = new CurlResponseList([$a, $b]);

        $result = $list->map(fn (CurlResponse $r) => $r->url);

        $this->assertSame(['http://127.0.0.1/a', 'http://127.0.0.1/b'], $result);

        // if the closure returns null for any response, the whole map() call returns null
        $result = $list->map(fn (CurlResponse $r) => $r->url === 'http://127.0.0.1/b' ? null : $r->url);

        $this->assertNull($result);
    }

    public function testGetContent(): void
    {
        $a = $this->response('http://127.0.0.1/a');
        $b = $this->response('http://127.0.0.1/b');
        $list = new CurlResponseList([$a, $b]);

        // no connection was actually made (defaults=false, no real request), so content is null for both;
        // getContent() bails out to null as soon as it hits one
        $this->assertNull($list->getContent());
    }
}

<?php

namespace Stackstra\Tests\Curl;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\Curl;
use Stackstra\Curl\CurlEventArguments;
use Stackstra\Curl\CurlEvents;
use Stackstra\Curl\CurlResponse;
use Stackstra\Curl\CurlResponseList;
use Stackstra\Curl\CurlTask;
use Stackstra\Tests\TestCase;

#[CoversClass(CurlEventArguments::class)]
class CurlEventArgumentsTest extends TestCase
{
    public function testConstruct(): void
    {
        // every argument is optional, all default to null
        $args = new CurlEventArguments();

        $this->assertNull($args->curl);
        $this->assertNull($args->task);
        $this->assertNull($args->response);
        $this->assertNull($args->events);
        $this->assertNull($args->response_list);

        // each is stored verbatim when provided
        $curl          = new Curl();
        $task          = new CurlTask('http://127.0.0.1/placeholder');
        $response      = new CurlResponse($task);
        $events        = new CurlEvents();
        $response_list = new CurlResponseList();

        $args = new CurlEventArguments($curl, $task, $response, $events, $response_list);

        $this->assertSame($curl, $args->curl);
        $this->assertSame($task, $args->task);
        $this->assertSame($response, $args->response);
        $this->assertSame($events, $args->events);
        $this->assertSame($response_list, $args->response_list);
    }
}

<?php

namespace Stackstra\Tests\Curl;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\Curl;
use Stackstra\Curl\CurlOptions;
use Stackstra\Curl\CurlResponseList;
use Stackstra\Curl\CurlTask;
use Stackstra\Curl\CurlTasks;
use Stackstra\Curl\CurlThrottle;
use Stackstra\Tests\TestCase;

/**
 * curl_init()/curl_setopt() only ever record placeholder loopback URLs (http://127.0.0.1/...) here — nothing
 * in this file ever calls curl_multi_exec(), so no connection is ever attempted. query()'s connection loop and
 * fileGetContents() are therefore not covered: they cannot be exercised without an actual network round trip.
 */
#[CoversClass(Curl::class)]
class CurlTest extends TestCase
{
    private const URL = 'http://127.0.0.1/placeholder';

    public function testConstruct(): void
    {
        // no options: everything wired up with defaults
        $curl = new Curl();
        $this->assertInstanceOf(CurlOptions::class, $curl->options);
        $this->assertInstanceOf(CurlTasks::class, $curl->tasks);
        $this->assertInstanceOf(CurlThrottle::class, $curl->throttle);
        $this->assertSame(0, $curl->tasks->count());

        // explicit options are forwarded to CurlOptions
        $curl = new Curl([CurlOptions::OPTION_THREADS => 5]);
        $this->assertSame(5, $curl->options->threads());
    }

    public function testAdd(): void
    {
        // string: dispatched to addURL()
        $curl = new Curl();
        $result = $curl->add(self::URL);
        $this->assertSame($curl, $result);
        $this->assertSame(1, $curl->tasks->count());

        // CurlTask object, id omitted: added as-is, its own id untouched
        $curl = new Curl();
        $task = new CurlTask(self::URL, 'original-id', defaults: false);
        $curl->add($task);
        $this->assertSame('original-id', $task->id);
        $this->assertSame(1, $curl->tasks->count());

        // CurlTask object, explicit id: overwrites the task's id before adding
        $curl = new Curl();
        $task = new CurlTask(self::URL, 'original-id', defaults: false);
        $curl->add($task, 'overridden-id');
        $this->assertSame('overridden-id', $task->id);

        // array of strings, keyed by id: each recurses back through add() with that key as the id
        $curl = new Curl();
        $curl->add([self::URL . '/a', self::URL . '/b']);
        $this->assertSame(2, $curl->tasks->count());

        // array of CurlTask objects
        $curl = new Curl();
        $curl->add([new CurlTask(self::URL, defaults: false), new CurlTask(self::URL, defaults: false)]);
        $this->assertSame(2, $curl->tasks->count());
    }

    public function testAddURL(): void
    {
        $curl = new Curl();

        // id omitted: CurlTask defaults it to the url itself
        $result = $curl->addURL(self::URL);
        $this->assertSame($curl, $result);
        $tasks = $curl->tasks->get();
        $task = reset($tasks);
        $this->assertSame(self::URL, $task->id);

        // explicit id
        $curl = new Curl();
        $curl->addURL(self::URL, 'my-id');
        $tasks = $curl->tasks->get();
        $task = reset($tasks);
        $this->assertSame('my-id', $task->id);
    }

    public function testAddTask(): void
    {
        $curl = new Curl();
        $task = new CurlTask(self::URL, defaults: false);

        $result = $curl->addTask($task);

        $this->assertSame($curl, $result);
        $this->assertSame(1, $curl->tasks->count());
    }

    public function testQuery(): void
    {
        // empty task list: warns and returns an empty response list without ever touching curl_multi_*()
        $curl = new Curl();

        $result = $this->silently(fn () => $curl->query());

        $this->assertInstanceOf(CurlResponseList::class, $result);
        $this->assertSame([], $result->get());
    }
}

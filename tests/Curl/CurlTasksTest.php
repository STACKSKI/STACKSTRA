<?php

namespace Stackstra\Tests\Curl;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\CurlOptions;
use Stackstra\Curl\CurlTask;
use Stackstra\Curl\CurlTasks;
use Stackstra\Tests\TestCase;

#[CoversClass(CurlTasks::class)]
class CurlTasksTest extends TestCase
{
    private function tasks(): CurlTasks
    {
        return new CurlTasks(new CurlOptions());
    }

    private function task(string $url = 'http://127.0.0.1/placeholder'): CurlTask
    {
        return new CurlTask($url, defaults: false);
    }

    public function testAdd(): void
    {
        $tasks = $this->tasks();
        $task  = $this->task();

        $tasks->add($task);

        $this->assertSame(1, $tasks->count());
        $this->assertSame(1, $tasks->countIncomplete());
        $this->assertNotNull($task->id_internal);
    }

    public function testCount(): void
    {
        $tasks = $this->tasks();

        $this->assertSame(0, $tasks->count());

        $tasks->add($this->task());
        $this->assertSame(1, $tasks->count());
    }

    public function testCountIncomplete(): void
    {
        $tasks = $this->tasks();
        $tasks->add($task = $this->task());

        $this->assertSame(1, $tasks->countIncomplete());

        $tasks->complete($task->id_internal);
        $this->assertSame(0, $tasks->countIncomplete());
    }

    public function testCountAborted(): void
    {
        $tasks = $this->tasks();
        $tasks->add($task = $this->task());

        $this->assertSame(0, $tasks->countAborted());

        $tasks->abort($task->id_internal);
        $this->assertSame(1, $tasks->countAborted());
    }

    public function testCountComplete(): void
    {
        $tasks = $this->tasks();
        $tasks->add($task = $this->task());

        $this->assertSame(0, $tasks->countComplete());

        $tasks->complete($task->id_internal);
        $this->assertSame(1, $tasks->countComplete());
    }

    public function testHasIncomplete(): void
    {
        $tasks = $this->tasks();
        $this->assertFalse($tasks->hasIncomplete());

        $tasks->add($task = $this->task());
        $this->assertTrue($tasks->hasIncomplete());

        $tasks->complete($task->id_internal);
        $this->assertFalse($tasks->hasIncomplete());
    }

    public function testHasAborted(): void
    {
        $tasks = $this->tasks();
        $this->assertFalse($tasks->hasAborted());

        $tasks->add($task = $this->task());
        $tasks->abort($task->id_internal);
        $this->assertTrue($tasks->hasAborted());
    }

    public function testGet(): void
    {
        $tasks = $this->tasks();
        $tasks->add($task = $this->task());

        $this->assertSame([$task->id_internal => $task], $tasks->get());
    }

    public function testGetIncomplete(): void
    {
        $tasks = $this->tasks();
        $tasks->add($a = $this->task('http://127.0.0.1/a'));
        $tasks->add($b = $this->task('http://127.0.0.1/b'));

        // no limit: all incomplete tasks
        $result = $tasks->getIncomplete();
        $this->assertCount(2, $result);

        // explicit limit
        $result = $tasks->getIncomplete(1);
        $this->assertCount(1, $result);
        $this->assertSame($a, $result[0]);
    }

    public function testGetComplete(): void
    {
        $tasks = $this->tasks();
        $tasks->add($a = $this->task('http://127.0.0.1/a'));
        $tasks->add($b = $this->task('http://127.0.0.1/b'));

        $tasks->complete($a->id_internal);

        $result = $tasks->getComplete();

        $this->assertCount(1, $result);
        $this->assertSame($a, reset($result));
    }

    public function testGetAborted(): void
    {
        $tasks = $this->tasks();
        $tasks->add($a = $this->task('http://127.0.0.1/a'));
        $tasks->add($b = $this->task('http://127.0.0.1/b'));

        $tasks->abort($a->id_internal);
        $tasks->abort($b->id_internal);

        // no limit
        $this->assertCount(2, $tasks->getAborted());

        // explicit limit
        $this->assertCount(1, $tasks->getAborted(1));
    }

    public function testComplete(): void
    {
        $tasks = $this->tasks();
        $tasks->add($task = $this->task());

        $result = $tasks->complete($task->id_internal);

        $this->assertSame($tasks, $result);
        $this->assertSame(0, $tasks->countIncomplete());
    }

    public function testAbort(): void
    {
        $tasks = $this->tasks();
        $tasks->add($task = $this->task());

        $result = $tasks->abort($task->id_internal);

        $this->assertSame($tasks, $result);
        $this->assertSame(0, $tasks->countIncomplete());
        $this->assertSame(1, $tasks->countAborted());
    }

    public function testAbortIncomplete(): void
    {
        $tasks = $this->tasks();
        $tasks->add($a = $this->task('http://127.0.0.1/a'));
        $tasks->add($b = $this->task('http://127.0.0.1/b'));

        $tasks->complete($a->id_internal);

        $result = $tasks->abortIncomplete();

        $this->assertSame($tasks, $result);
        $this->assertSame(0, $tasks->countIncomplete());
        $this->assertSame(1, $tasks->countAborted()); // only b, a was already complete
    }
}

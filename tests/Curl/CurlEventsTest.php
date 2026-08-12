<?php

namespace Stackstra\Tests\Curl;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\CurlEvents;
use Stackstra\Tests\TestCase;

#[CoversClass(CurlEvents::class)]
class CurlEventsTest extends TestCase
{
    public function testMake(): void
    {
        // no callbacks
        $events = CurlEvents::make();
        $this->assertFalse($events->hasOnSuccess());

        // named-argument callbacks are set immediately
        $events = CurlEvents::make(onSuccess: fn () => 'ok');
        $this->assertTrue($events->hasOnSuccess());
    }

    public function testEventNames(): void
    {
        $names = CurlEvents::eventNames();

        $this->assertSame([
            'onCompleteAll' => 'onCompleteAll',
            'onComplete'    => 'onComplete',
            'onSuccess'     => 'onSuccess',
            'onError'       => 'onError',
            'onAbort'       => 'onAbort',
            'onAbortAll'    => 'onAbortAll',
        ], $names);
    }

    public function testHasEventName(): void
    {
        $this->assertTrue(CurlEvents::make()->hasEventName('onSuccess'));
        $this->assertFalse(CurlEvents::make()->hasEventName('onBogus'));
    }

    public function testSetOnCompleteAll(): void
    {
        $events = new CurlEvents();

        $result = $events->setOnCompleteAll(fn () => null);

        $this->assertSame($events, $result);
        $this->assertTrue($events->hasOnCompleteAll());
    }

    public function testSetOnComplete(): void
    {
        $events = new CurlEvents();

        $events->setOnComplete(fn () => null);

        $this->assertTrue($events->hasOnComplete());
    }

    public function testSetOnSuccess(): void
    {
        $events = new CurlEvents();

        $events->setOnSuccess(fn () => null);

        $this->assertTrue($events->hasOnSuccess());
    }

    public function testSetOnError(): void
    {
        $events = new CurlEvents();

        $events->setOnError(fn () => null);

        $this->assertTrue($events->hasOnError());
    }

    public function testSetOnAbort(): void
    {
        $events = new CurlEvents();

        $events->setOnAbort(fn () => null);

        $this->assertTrue($events->hasOnAbort());
    }

    public function testSetOnAbortAll(): void
    {
        $events = new CurlEvents();

        $events->setOnAbortAll(fn () => null);

        $this->assertTrue($events->hasOnAbortAll());
    }

    public function testTriggerOnCompleteAll(): void
    {
        $received = null;
        $events = (new CurlEvents())->setOnCompleteAll(function ($arg) use (&$received) { $received = $arg; return 'result'; });

        $this->assertSame('result', $events->triggerOnCompleteAll('x'));
        $this->assertSame('x', $received);
        $this->assertSame(1, $events->countCompleteAll());

        // no callback registered: null, and the counter stays at 0
        $empty = new CurlEvents();
        $this->assertNull($empty->triggerOnCompleteAll('x'));
        $this->assertSame(0, $empty->countCompleteAll());
    }

    public function testTriggerOnComplete(): void
    {
        $events = (new CurlEvents())->setOnComplete(fn () => 'result');

        $this->assertSame('result', $events->triggerOnComplete());
        $this->assertSame(1, $events->countComplete());
    }

    public function testTriggerOnSuccess(): void
    {
        $events = (new CurlEvents())->setOnSuccess(fn () => 'result');

        $this->assertSame('result', $events->triggerOnSuccess());
        $this->assertSame(1, $events->countSuccess());
    }

    public function testTriggerOnError(): void
    {
        $events = (new CurlEvents())->setOnError(fn () => 'result');

        $this->assertSame('result', $events->triggerOnError());
        $this->assertSame(1, $events->countError());
    }

    public function testTriggerOnAbort(): void
    {
        $events = (new CurlEvents())->setOnAbort(fn () => 'result');

        $this->assertSame('result', $events->triggerOnAbort());
        $this->assertSame(1, $events->countAbort());
    }

    public function testTriggerOnAbortAll(): void
    {
        $events = (new CurlEvents())->setOnAbortAll(fn () => 'result');

        $this->assertSame('result', $events->triggerOnAbortAll());
    }

    public function testHasOnCompleteAll(): void
    {
        $events = new CurlEvents();
        $this->assertFalse($events->hasOnCompleteAll());

        $events->setOnCompleteAll(fn () => null);
        $this->assertTrue($events->hasOnCompleteAll());
    }

    public function testHasOnComplete(): void
    {
        $events = new CurlEvents();
        $this->assertFalse($events->hasOnComplete());
    }

    public function testHasOnSuccess(): void
    {
        $events = new CurlEvents();
        $this->assertFalse($events->hasOnSuccess());
    }

    public function testHasOnError(): void
    {
        $events = new CurlEvents();
        $this->assertFalse($events->hasOnError());
    }

    public function testHasOnAbort(): void
    {
        $events = new CurlEvents();
        $this->assertFalse($events->hasOnAbort());
    }

    public function testHasOnAbortAll(): void
    {
        $events = new CurlEvents();
        $this->assertFalse($events->hasOnAbortAll());
    }

    public function testUnsetOnCompleteAll(): void
    {
        $callback = fn () => null;
        $events = (new CurlEvents())->setOnCompleteAll($callback);

        $result = $events->unsetOnCompleteAll();

        $this->assertSame($callback, $result);
        $this->assertFalse($events->hasOnCompleteAll());

        // already unset: null
        $this->assertNull($events->unsetOnCompleteAll());
    }

    public function testUnsetOnComplete(): void
    {
        $events = (new CurlEvents())->setOnComplete(fn () => null);

        $events->unsetOnComplete();

        $this->assertFalse($events->hasOnComplete());
    }

    public function testUnsetOnSuccess(): void
    {
        $events = (new CurlEvents())->setOnSuccess(fn () => null);

        $events->unsetOnSuccess();

        $this->assertFalse($events->hasOnSuccess());
    }

    public function testUnsetOnError(): void
    {
        $events = (new CurlEvents())->setOnError(fn () => null);

        $events->unsetOnError();

        $this->assertFalse($events->hasOnError());
    }

    public function testUnsetOnAbort(): void
    {
        $events = (new CurlEvents())->setOnAbort(fn () => null);

        $events->unsetOnAbort();

        $this->assertFalse($events->hasOnAbort());
    }

    public function testUnsetOnAbortAll(): void
    {
        $events = (new CurlEvents())->setOnAbortAll(fn () => null);

        $events->unsetOnAbortAll();

        $this->assertFalse($events->hasOnAbortAll());
    }

    public function testCounters(): void
    {
        $events = (new CurlEvents())->setOnSuccess(fn () => null);
        $events->triggerOnSuccess();
        $events->triggerOnSuccess();

        $this->assertSame(['onSuccess' => 2], $events->counters());
    }

    public function testCountersReset(): void
    {
        $events = (new CurlEvents())->setOnSuccess(fn () => null);
        $events->triggerOnSuccess();

        $result = $events->countersReset();

        $this->assertSame($events, $result);
        $this->assertSame([], $events->counters());
    }

    public function testCount(): void
    {
        $events = (new CurlEvents())->setOnSuccess(fn () => null);

        $this->assertSame(0, $events->count('onSuccess'));

        $events->triggerOnSuccess();
        $this->assertSame(1, $events->count('onSuccess'));
    }

    public function testCountCompleteAll(): void
    {
        $events = (new CurlEvents())->setOnCompleteAll(fn () => null);
        $events->triggerOnCompleteAll();

        $this->assertSame(1, $events->countCompleteAll());
    }

    public function testCountComplete(): void
    {
        $events = (new CurlEvents())->setOnComplete(fn () => null);
        $events->triggerOnComplete();

        $this->assertSame(1, $events->countComplete());
    }

    public function testCountSuccess(): void
    {
        $events = (new CurlEvents())->setOnSuccess(fn () => null);
        $events->triggerOnSuccess();

        $this->assertSame(1, $events->countSuccess());
    }

    public function testCountError(): void
    {
        $events = (new CurlEvents())->setOnError(fn () => null);
        $events->triggerOnError();

        $this->assertSame(1, $events->countError());
    }

    public function testCountAbort(): void
    {
        $events = (new CurlEvents())->setOnAbort(fn () => null);
        $events->triggerOnAbort();

        $this->assertSame(1, $events->countAbort());
    }
}

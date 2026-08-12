<?php

namespace Stackstra\Tests\Curl;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\CurlOptions;
use Stackstra\Curl\CurlThrottle;
use Stackstra\Tests\TestCase;

#[CoversClass(CurlThrottle::class)]
class CurlThrottleTest extends TestCase
{
    public function testConstruct(): void
    {
        $throttle = new CurlThrottle(new CurlOptions());

        $this->assertNull($throttle->slots());
        $this->assertFalse($throttle->isActive());
    }

    public function testSlots(): void
    {
        $throttle = new CurlThrottle(new CurlOptions([CurlOptions::OPTION_THROTTLE_SLOTS => 5]));

        $this->assertSame(5, $throttle->slots());
    }

    public function testInterval(): void
    {
        $throttle = new CurlThrottle(new CurlOptions([CurlOptions::OPTION_THROTTLE_INTERVAL => 60]));

        $this->assertSame(60, $throttle->interval());
    }

    public function testIsActive(): void
    {
        // neither slots nor interval set: inactive
        $this->assertFalse((new CurlThrottle(new CurlOptions()))->isActive());

        // only slots set: still inactive
        $this->assertFalse((new CurlThrottle(new CurlOptions([CurlOptions::OPTION_THROTTLE_SLOTS => 5])))->isActive());

        // both set: active
        $throttle = new CurlThrottle(new CurlOptions([
            CurlOptions::OPTION_THROTTLE_SLOTS    => 5,
            CurlOptions::OPTION_THROTTLE_INTERVAL => 60,
        ]));
        $this->assertTrue($throttle->isActive());
    }

    public function testSlotsUsed(): void
    {
        $throttle = new CurlThrottle(new CurlOptions());

        $this->assertSame(0, $throttle->slotsUsed());

        $throttle->log();
        $this->assertSame(1, $throttle->slotsUsed());
    }

    public function testSlotsAvailable(): void
    {
        $throttle = new CurlThrottle(new CurlOptions([CurlOptions::OPTION_THROTTLE_SLOTS => 3]));

        $this->assertSame(3, $throttle->slotsAvailable());

        $throttle->log();
        $this->assertSame(2, $throttle->slotsAvailable());
    }

    public function testHasSlotsAvailable(): void
    {
        $throttle = new CurlThrottle(new CurlOptions([CurlOptions::OPTION_THROTTLE_SLOTS => 1]));

        $this->assertTrue($throttle->hasSlotsAvailable());

        $throttle->log();
        $this->assertFalse($throttle->hasSlotsAvailable());
    }

    public function testIntervalStartedAt(): void
    {
        $throttle = new CurlThrottle(new CurlOptions());

        // no timestamps logged yet: null
        $this->assertNull($throttle->intervalStartedAt());

        $throttle->log(100.0);
        $throttle->log(200.0);

        // the first logged timestamp
        $this->assertSame(100.0, $throttle->intervalStartedAt());
    }

    public function testIntervalNextAt(): void
    {
        $throttle = new CurlThrottle(new CurlOptions([CurlOptions::OPTION_THROTTLE_INTERVAL => 60]));

        // nothing logged: null
        $this->assertNull($throttle->intervalNextAt());

        $throttle->log(100.0);
        $this->assertSame(160.0, $throttle->intervalNextAt());

        // interval unset: null even with a logged timestamp
        $throttle2 = new CurlThrottle(new CurlOptions());
        $throttle2->log(100.0);
        $this->assertNull($throttle2->intervalNextAt());
    }

    public function testIntervalReset(): void
    {
        $throttle = new CurlThrottle(new CurlOptions());
        $throttle->log();

        $throttle->intervalReset();

        $this->assertSame(0, $throttle->slotsUsed());
    }

    public function testLog(): void
    {
        $throttle = new CurlThrottle(new CurlOptions());

        // explicit timestamp
        $throttle->log(123.0);
        $this->assertSame(123.0, $throttle->intervalStartedAt());

        // omitted: uses the current time, still just adds one more logged slot
        $throttle->log();

        $this->assertSame(2, $throttle->slotsUsed());
    }

    public function testTrigger(): void
    {
        // inactive throttling: limited only by threads and the incomplete-task count
        $throttle = new CurlThrottle(new CurlOptions([CurlOptions::OPTION_THREADS => 3]));
        $this->assertSame(3, $throttle->trigger(count_tasks_incomplete: 10));
        $this->assertSame(3, $throttle->trigger(count_tasks_incomplete: 5));
        $this->assertSame(2, $throttle->trigger(count_tasks_incomplete: 2));

        // active throttling, slots available: limited by threads/incomplete/available slots
        // (interval() is typed ?int, so a fractional interval would truncate to 0/inactive — use whole seconds)
        $throttle = new CurlThrottle(new CurlOptions([
            CurlOptions::OPTION_THREADS           => 10,
            CurlOptions::OPTION_THROTTLE_SLOTS     => 2,
            CurlOptions::OPTION_THROTTLE_INTERVAL  => 1,
        ]));
        $this->assertSame(2, $throttle->trigger(count_tasks_incomplete: 10, now: 0.0));

        // active throttling, interval already elapsed by the time trigger() is called: resets without sleeping
        $throttle->log(0.0);
        $throttle->log(0.0);
        $limit = $throttle->trigger(count_tasks_incomplete: 10, now: 5.0);

        $this->assertSame(2, $limit); // slots reset, full slots available again

        // no slots available, interval NOT yet elapsed: sleeps the remaining (tiny) interval, then resets
        $throttle2 = new CurlThrottle(new CurlOptions([
            CurlOptions::OPTION_THREADS           => 10,
            CurlOptions::OPTION_THROTTLE_SLOTS     => 1,
            CurlOptions::OPTION_THROTTLE_INTERVAL  => 1,
        ]));
        $throttle2->log(0.0);

        $start = microtime(true);
        $limit = $throttle2->trigger(count_tasks_incomplete: 10, now: 0.999);
        $elapsed = microtime(true) - $start;

        $this->assertSame(1, $limit);
        $this->assertGreaterThan(0, $elapsed);
        $this->assertLessThan(1, $elapsed); // sleeps ~0.001s (intervalNextAt - now), not the full interval
    }
}

<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\Timer;
use Stackstra\Tests\TestCase;

#[CoversClass(Timer::class)]
class TimerTest extends TestCase
{
    public function testConstruct(): void
    {
        // autostart=true (default): started immediately, diff() works right away
        $timer = new Timer();

        $this->assertGreaterThanOrEqual(0, $timer->diff());

        // autostart=false: not started, diff() throws (no $started property set)
        $timer = new Timer(false);

        $this->expectException(\Error::class);
        $timer->diff();
    }

    public function testInit(): void
    {
        $timer = Timer::init();

        $this->assertInstanceOf(Timer::class, $timer);
        $this->assertGreaterThanOrEqual(0, $timer->diff());
    }

    public function testStart(): void
    {
        $timer = new Timer(false);

        $result = $timer->start();

        $this->assertSame($timer, $result);
        $this->assertGreaterThanOrEqual(0, $timer->diff());
    }

    public function testDiff(): void
    {
        $timer = new Timer();

        usleep(1000);

        $this->assertGreaterThan(0, $timer->diff());
    }

    public function testDiffMilliseconds(): void
    {
        $timer = new Timer();

        usleep(2000);

        // default precision: no rounding
        $ms = $timer->diffMilliseconds();
        $this->assertGreaterThan(0, $ms);

        // explicit precision
        $rounded = $timer->diffMilliseconds(0);
        $this->assertSame((float) round($rounded), $rounded);
    }
}

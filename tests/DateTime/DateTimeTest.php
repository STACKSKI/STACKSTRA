<?php

namespace Stackstra\Tests\DateTime;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\DateTime\DateTime;
use Stackstra\Tests\TestCase;

#[CoversClass(DateTime::class)]
class DateTimeTest extends TestCase
{
    public function testConstructAndMake(): void
    {
        $timestamp = $this->faker->unixTime();

        // explicit timestamp is honored, for both the constructor and make()
        $this->assertSame(date('Ymd', $timestamp), (new DateTime($timestamp))->formatYmd());
        $this->assertSame(date('Ymd', $timestamp), DateTime::make($timestamp)->formatYmd());

        // optional argument omitted: falls back to the current time
        $now = time();
        $this->assertSame(date('Ymd', $now), (new DateTime())->formatYmd());
        $this->assertSame(date('Ymd', $now), DateTime::make()->formatYmd());
    }

    public function testFormat(): void
    {
        $timestamp = $this->faker->unixTime();
        $dt        = new DateTime($timestamp);

        $this->assertSame(date('Y-m-d H:i:s', $timestamp), $dt->format('Y-m-d H:i:s'));
        $this->assertSame(date('H:i', $timestamp), $dt->format('H:i'));
    }

    public function testFormatYmd(): void
    {
        $timestamp = $this->faker->unixTime();

        $this->assertSame(date('Ymd', $timestamp), (new DateTime($timestamp))->formatYmd());
    }

    public function testFormatYmdHis(): void
    {
        $timestamp = $this->faker->unixTime();

        $this->assertSame(date('YmdHis', $timestamp), (new DateTime($timestamp))->formatYmdHis());
    }
}

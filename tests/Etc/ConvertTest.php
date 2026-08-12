<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\Convert;

#[CoversClass(Convert::class)]
class ConvertTest extends TestCase
{
    public function testSecondsToNanoseconds(): void
    {
        $this->assertSame(2000000000.0, Convert::secondsToNanoseconds(2, 0));
    }

    public function testSecondsToMicroseconds(): void
    {
        $this->assertSame(2000000.0, Convert::secondsToMicroseconds(2, 0));
    }

    public function testSecondsToMilliseconds(): void
    {
        $this->assertSame(2000.0, Convert::secondsToMilliseconds(2, 0));
    }

    public function testSecondsToMinutes(): void
    {
        $this->assertSame(2.0, Convert::secondsToMinutes(120, 0));
    }

    public function testSecondsToHours(): void
    {
        $this->assertSame(2.0, Convert::secondsToHours(7200, 0));
    }

    public function testSecondsToDays(): void
    {
        $this->assertSame(2.0, Convert::secondsToDays(172800, 0));
    }

    public function testSecondsToWeeks(): void
    {
        $this->assertSame(2.0, Convert::secondsToWeeks(1209600, 0));
    }

    public function testSecondsToMonths(): void
    {
        $this->assertSame(2.0, Convert::secondsToMonths(5184000, 0));
    }

    public function testSecondsToMonths28(): void
    {
        $this->assertSame(2.0, Convert::secondsToMonths28(4838400, 0));
    }

    public function testSecondsToMonths29(): void
    {
        $this->assertSame(2.0, Convert::secondsToMonths29(5011200, 0));
    }

    public function testSecondsToMonths30(): void
    {
        $this->assertSame(2.0, Convert::secondsToMonths30(5184000, 0));
    }

    public function testSecondsToMonths31(): void
    {
        $this->assertSame(2.0, Convert::secondsToMonths31(5356800, 0));
    }

    public function testSecondsToYears(): void
    {
        $this->assertSame(2.0, Convert::secondsToYears(63072000, 0));
    }

    public function testSecondsToYearsLeap(): void
    {
        $this->assertSame(2.0, Convert::secondsToYearsLeap(63244800, 0));
    }

    public function testDaysToSeconds(): void
    {
        $this->assertSame(172800, Convert::daysToSeconds(2));
    }

    public function testBytesToKilobytes(): void
    {
        $this->assertSame(2, Convert::bytesToKilobytes(2048));
    }

    public function testBytesToMegabytes(): void
    {
        $this->assertSame(2, Convert::bytesToMegabytes(2097152));
    }

    public function testBytesToGigabytes(): void
    {
        $this->assertSame(2, Convert::bytesToGigabytes(2147483648));
    }

    public function testKilobytesToBytes(): void
    {
        $this->assertSame(2048, Convert::kilobytesToBytes(2));
    }

    public function testMegabytesToBytes(): void
    {
        $this->assertSame(2097152, Convert::megabytesToBytes(2));
    }

    public function testMicrosecondsToSeconds(): void
    {
        $this->assertSame(2.0E-6, Convert::microsecondsToSeconds(2, 6));
    }

    public function testMicrosecondsToMilliseconds(): void
    {
        $this->assertSame(2.0, Convert::microsecondsToMilliseconds(2000, 0));
    }

    public function testNanosecondsToSeconds(): void
    {
        $this->assertSame(2.0, Convert::nanosecondsToSeconds(2000000000, 0));
    }
}

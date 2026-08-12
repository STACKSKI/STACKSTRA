<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\Date;

#[CoversClass(Date::class)]
class DateTest extends TestCase
{
    private const TIMESTAMP = 1623767445; // 2021-06-15 14:30:45 UTC (Tuesday)

    protected function setUp(): void
    {
        date_default_timezone_set('UTC');
    }

    public function testTimezoneSet(): void
    {
        $this->assertTrue(Date::timezoneSet('Europe/Minsk'));
    }

    public function testTimezoneGet(): void
    {
        Date::timezoneSet('Europe/Minsk');

        $this->assertSame('Europe/Minsk', Date::timezoneGet());
    }

    public function testDate(): void
    {
        $this->assertSame('2021-06-15', Date::date('-', self::TIMESTAMP));

        $this->assertSame('2021/06/15', Date::date('/', self::TIMESTAMP));
    }

    public function testTime(): void
    {
        $this->assertSame('14:30:45', Date::time(':', self::TIMESTAMP));

        $this->assertSame('14-30-45', Date::time('-', self::TIMESTAMP));
    }

    public function testHour(): void
    {
        $this->assertMatchesRegularExpression('/^\d{2}$/', Date::hour());
    }

    public function testMinute(): void
    {
        $this->assertMatchesRegularExpression('/^\d{2}$/', Date::minute());
    }

    public function testTimestamp(): void
    {
        $this->assertIsInt(Date::timestamp());

        $this->assertIsString(Date::timestamp(true, false));
    }

    public function testDatetime(): void
    {
        $this->assertSame('2021-06-15 14:30:45', Date::datetime('-', ':', self::TIMESTAMP));

        $this->assertNull(Date::datetime('-', ':', -1));
    }

    public function testDay(): void
    {
        $this->assertSame(166, Date::day(self::TIMESTAMP));
    }

    public function testWeek(): void
    {
        $this->assertSame(24, Date::week(self::TIMESTAMP));
    }

    public function testMonth(): void
    {
        $this->assertSame('06', Date::month(self::TIMESTAMP));
    }

    public function testYear(): void
    {
        $this->assertSame('2021', Date::year(self::TIMESTAMP));
    }

    public function testDaysInYear(): void
    {
        $this->assertSame(366, Date::daysInYear(2004));

        $this->assertSame(365, Date::daysInYear(2010));
    }

    public function testDaysInMonth(): void
    {
        $this->assertSame(30, Date::daysInMonth(1970, 4));
        $this->assertSame(31, Date::daysInMonth(1970, 5));

        $this->assertSame(29, Date::daysInMonth(2004, 2));
        $this->assertSame(29, Date::daysInMonth(2000, 2));
        $this->assertSame(28, Date::daysInMonth(2100, 2));
    }

    public function testToTimestamp(): void
    {
        $this->assertIsInt(Date::toTimestamp('2021-06-15', 'Y-m-d'));

        $this->assertNull(Date::toTimestamp('notadate', 'Y-m-d'));
    }

    public function testWeeksInMonth(): void
    {
        $this->assertSame(5, Date::weeksInMonth(2021, 6));
    }

    public function testWeekBorders(): void
    {
        $this->assertSame(['2021-06-14', '2021-06-21'], Date::weekBorders(2021, 24));
    }

    public function testWeeksInYear(): void
    {
        $this->assertSame(53, Date::weeksInYear(2020));

        $this->assertSame(52, Date::weeksInYear(2021));
    }

    public function testFirstDayOfMonth(): void
    {
        $this->assertSame('1', Date::firstDayOfMonth(2021, 6));
    }

    public function testLastDayOfMonth(): void
    {
        $this->assertSame('30', Date::lastDayOfMonth(2021, 6));
    }

    public function testFirstDayOfWeek(): void
    {
        $this->assertSame('1', Date::firstDayOfWeek(2021, 6, 1)); // week 1 -> first day of month

        $this->assertSame(0, Date::firstDayOfWeek(2021, 6, 99)); // beyond last week
    }

    public function testLastDayOfWeek(): void
    {
        $this->assertSame('30', Date::lastDayOfWeek(2021, 6, 5)); // last week -> last day of month

        $this->assertSame(0, Date::lastDayOfWeek(2021, 6, 99)); // beyond last week
    }

    public function testCalendar(): void
    {
        $calendar = Date::calendar(2021);

        $this->assertSame(2021, $calendar->id);
        $this->assertSame(52, $calendar->total_weeks);
        $this->assertSame(365, $calendar->total_days);
        $this->assertCount(12, $calendar->months);
        $this->assertSame(31, $calendar->months[1]->total_days);
    }

    public function testDayOfWeek(): void
    {
        $this->assertSame(2, Date::dayOfWeek(self::TIMESTAMP)); // Tuesday

        $this->assertSame(7, Date::dayOfWeek(strtotime('2021-06-13'))); // Sunday -> 7
    }

    public function testIsMonday(): void
    {
        $this->assertTrue(Date::isMonday(strtotime('2021-06-14')));

        $this->assertFalse(Date::isMonday(self::TIMESTAMP));
    }

    public function testIsTuesday(): void
    {
        $this->assertTrue(Date::isTuesday(self::TIMESTAMP));

        $this->assertFalse(Date::isTuesday(strtotime('2021-06-14')));
    }

    public function testIsWednesday(): void
    {
        $this->assertTrue(Date::isWednesday(strtotime('2021-06-16')));

        $this->assertFalse(Date::isWednesday(self::TIMESTAMP));
    }

    public function testIsThursday(): void
    {
        $this->assertTrue(Date::isThursday(strtotime('2021-06-17')));

        $this->assertFalse(Date::isThursday(self::TIMESTAMP));
    }

    public function testIsFriday(): void
    {
        $this->assertTrue(Date::isFriday(strtotime('2021-06-18')));

        $this->assertFalse(Date::isFriday(self::TIMESTAMP));
    }

    public function testIsSaturday(): void
    {
        $this->assertTrue(Date::isSaturday(strtotime('2021-06-19')));

        $this->assertFalse(Date::isSaturday(self::TIMESTAMP));
    }

    public function testIsSunday(): void
    {
        $this->assertTrue(Date::isSunday(strtotime('2021-06-13')));

        $this->assertFalse(Date::isSunday(self::TIMESTAMP));
    }
}

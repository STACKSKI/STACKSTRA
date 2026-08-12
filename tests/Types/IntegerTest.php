<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Integer;

#[CoversClass(Integer::class)]
class IntegerTest extends TestCase
{
    public function testRand(): void
    {
        $this->assertSame(7, Integer::rand(7, 7));

        $value = Integer::rand(1, 5);

        $this->assertGreaterThanOrEqual(1, $value);
        $this->assertLessThanOrEqual(5, $value);
    }

    public function testRandInt32(): void
    {
        $value = Integer::randInt32();

        $this->assertGreaterThanOrEqual(Integer::INT32_MIN, $value);
        $this->assertLessThanOrEqual(Integer::INT32_MAX, $value);
    }

    public function testRandUint32(): void
    {
        $value = Integer::randUint32();

        $this->assertGreaterThanOrEqual(Integer::UINT32_MIN, $value);
        $this->assertLessThanOrEqual(Integer::UINT32_MAX, $value);
    }

    public function testRandUnique(): void
    {
        $result = Integer::randUnique(1, 5, 5);

        sort($result);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testIsOdd(): void
    {
        $this->assertTrue(Integer::isOdd(1));
        $this->assertTrue(Integer::isOdd(3));
        $this->assertTrue(Integer::isOdd(-1));

        $this->assertFalse(Integer::isOdd(2));
        $this->assertFalse(Integer::isOdd(0));
    }

    public function testIsEven(): void
    {
        $this->assertTrue(Integer::isEven(2));
        $this->assertTrue(Integer::isEven(0));
        $this->assertTrue(Integer::isEven(-2));

        $this->assertFalse(Integer::isEven(1));
        $this->assertFalse(Integer::isEven(3));
    }

    public function testIsPositive(): void
    {
        $this->assertTrue(Integer::isPositive(5));

        $this->assertFalse(Integer::isPositive(0));
        $this->assertFalse(Integer::isPositive(-5));
    }

    public function testIsNegative(): void
    {
        $this->assertTrue(Integer::isNegative(-5));

        $this->assertFalse(Integer::isNegative(0));
        $this->assertFalse(Integer::isNegative(5));
    }

    public function testToBin(): void
    {
        $this->assertSame('0', Integer::toBin(0));
        $this->assertSame('101', Integer::toBin(5));
        $this->assertSame('11111111', Integer::toBin(255));
    }

    public function testToHex(): void
    {
        $this->assertSame('0', Integer::toHex(0));
        $this->assertSame('10', Integer::toHex(16));
        $this->assertSame('ff', Integer::toHex(255));
    }

    public function testInRange(): void
    {
        $this->assertTrue(Integer::inRange(5, 1, 10));
        $this->assertTrue(Integer::inRange(1, 1, 10));
        $this->assertTrue(Integer::inRange(10, 1, 10));
        $this->assertFalse(Integer::inRange(0, 1, 10));

        $this->assertTrue(Integer::inRange(5, 1, 10, false));
        $this->assertFalse(Integer::inRange(1, 1, 10, false));
        $this->assertFalse(Integer::inRange(10, 1, 10, false));
    }
}

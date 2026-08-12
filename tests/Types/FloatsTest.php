<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Floats;

#[CoversClass(Floats::class)]
class FloatsTest extends TestCase
{
    public function testRand(): void
    {
        $this->assertSame(5.0, Floats::rand(5, 5));

        $bounded = Floats::rand(1, 5, 2);
        $this->assertGreaterThanOrEqual(1.0, $bounded);
        $this->assertLessThanOrEqual(5.0, $bounded);
        $this->assertSame($bounded, round($bounded, 2));

        $this->assertSame(0.0, fmod(Floats::rand(0, 10, 0), 1.0));
    }

    public function testCeil(): void
    {
        $this->assertSame(5.0, Floats::ceil(4.1));
        $this->assertSame(5.0, Floats::ceil(5.0));
        $this->assertSame(-4.0, Floats::ceil(-4.9));
    }

    public function testFloor(): void
    {
        $this->assertSame(4.0, Floats::floor(4.9));
        $this->assertSame(5.0, Floats::floor(5.0));
        $this->assertSame(-5.0, Floats::floor(-4.1));
    }

    public function testRound(): void
    {
        $this->assertSame(3.14, Floats::round(3.14159));
        $this->assertSame(3.142, Floats::round(3.14159, 3));
        $this->assertSame(3.0, Floats::round(2.5, 0));
    }

    public function testIsGreater(): void
    {
        $this->assertTrue(Floats::isGreater(5.0, 3.0));
        $this->assertFalse(Floats::isGreater(3.0, 5.0));
        $this->assertFalse(Floats::isGreater(1.0, 1.0));
        $this->assertTrue(Floats::isGreater(1.00002, 1.0));
        $this->assertFalse(Floats::isGreater(1.000005, 1.0));
        $this->assertFalse(Floats::isGreater(0.00001, 0.0));
        $this->assertTrue(Floats::isGreater(1.0, 0.0, 0.5));
    }

    public function testIsLess(): void
    {
        $this->assertTrue(Floats::isLess(3.0, 5.0));
        $this->assertFalse(Floats::isLess(5.0, 3.0));
        $this->assertFalse(Floats::isLess(1.0, 1.0));
        $this->assertTrue(Floats::isLess(1.0, 1.00002));
        $this->assertFalse(Floats::isLess(1.0, 1.000005));
        $this->assertTrue(Floats::isLess(0.0, 1.0, 0.5));
    }

    public function testIsEqual(): void
    {
        $this->assertTrue(Floats::isEqual(1.0, 1.0));
        $this->assertTrue(Floats::isEqual(0.1 + 0.2, 0.3));
        $this->assertTrue(Floats::isEqual(1.0, 1.000005));
        $this->assertTrue(Floats::isEqual(0.0, 0.00001));
        $this->assertFalse(Floats::isEqual(1.0, 1.00002));
        $this->assertTrue(Floats::isEqual(1.0, 1.5, 0.6));
        $this->assertFalse(Floats::isEqual(1.0, 1.5, 0.4));
    }

    public function testIsNatural(): void
    {
        $this->assertTrue(Floats::isNatural(5.0));
        $this->assertTrue(Floats::isNatural(5.0000001)); // within epsilon of a whole number

        $this->assertFalse(Floats::isNatural(5.5));  // not whole
        $this->assertFalse(Floats::isNatural(0.0));  // zero is not a counting number
        $this->assertFalse(Floats::isNatural(-3.0)); // negatives are never natural
    }

    public function testIsGreaterOrEqual(): void
    {
        $this->assertTrue(Floats::isGreaterOrEqual(5.0, 3.0));
        $this->assertTrue(Floats::isGreaterOrEqual(1.0, 1.0));
        $this->assertFalse(Floats::isGreaterOrEqual(3.0, 5.0));
    }

    public function testIsLessOrEqual(): void
    {
        $this->assertTrue(Floats::isLessOrEqual(3.0, 5.0));
        $this->assertTrue(Floats::isLessOrEqual(1.0, 1.0));
        $this->assertFalse(Floats::isLessOrEqual(5.0, 3.0));
    }

    public function testIsNotNegative(): void
    {
        $this->assertTrue(Floats::isNotNegative(0.0));
        $this->assertTrue(Floats::isNotNegative(5.0));
        $this->assertTrue(Floats::isNotNegative(-0.000005));
        $this->assertFalse(Floats::isNotNegative(-5.0));
    }

    public function testIsNotPositive(): void
    {
        $this->assertTrue(Floats::isNotPositive(0.0));
        $this->assertTrue(Floats::isNotPositive(-5.0));
        $this->assertTrue(Floats::isNotPositive(0.000005));
        $this->assertFalse(Floats::isNotPositive(5.0));
    }

    public function testIsPositive(): void
    {
        $this->assertTrue(Floats::isPositive(5.0));
        $this->assertFalse(Floats::isPositive(0.0));
        $this->assertFalse(Floats::isPositive(0.000005));
        $this->assertFalse(Floats::isPositive(-5.0));
    }

    public function testIsNegative(): void
    {
        $this->assertTrue(Floats::isNegative(-5.0));
        $this->assertFalse(Floats::isNegative(0.0));
        $this->assertFalse(Floats::isNegative(-0.000005));
        $this->assertFalse(Floats::isNegative(5.0));
    }

    public function testIsZero(): void
    {
        $this->assertTrue(Floats::isZero(0.0));
        $this->assertTrue(Floats::isZero(0.000005));
        $this->assertFalse(Floats::isZero(0.00002));
        $this->assertFalse(Floats::isZero(1.0));
    }

    public function testIsBetween(): void
    {
        $this->assertTrue(Floats::isBetween(5.0, 0.0, 10.0));
        $this->assertFalse(Floats::isBetween(0.0, 0.0, 10.0));
        $this->assertFalse(Floats::isBetween(10.0, 0.0, 10.0));
        $this->assertFalse(Floats::isBetween(-1.0, 0.0, 10.0));
    }

    public function testIsBetweenOrEqual(): void
    {
        $this->assertTrue(Floats::isBetweenOrEqual(5.0, 0.0, 10.0));
        $this->assertTrue(Floats::isBetweenOrEqual(0.0, 0.0, 10.0));
        $this->assertTrue(Floats::isBetweenOrEqual(10.0, 0.0, 10.0));
        $this->assertFalse(Floats::isBetweenOrEqual(11.0, 0.0, 10.0));
    }

    public function testMin(): void
    {
        $this->assertSame(3.0, Floats::min(3.0, 5.0));
        $this->assertSame(3.0, Floats::min(5.0, 3.0));
        $this->assertSame(2.0, Floats::min(2.0, 2.0));
    }

    public function testMax(): void
    {
        $this->assertSame(5.0, Floats::max(3.0, 5.0));
        $this->assertSame(5.0, Floats::max(5.0, 3.0));
        $this->assertSame(2.0, Floats::max(2.0, 2.0));
    }
}

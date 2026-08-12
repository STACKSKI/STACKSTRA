<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Chars;

#[CoversClass(Chars::class)]
class CharsTest extends TestCase
{
    public function testNth(): void
    {
        $this->assertSame('H', Chars::nth('Hello', 1));
        $this->assertSame('o', Chars::nth('Hello', 5));

        $this->assertNull(Chars::nth('Hello', 0));
        $this->assertNull(Chars::nth('Hello', 6));
    }

    public function testCount(): void
    {
        $this->assertSame(5, Chars::count('Hello'));
        $this->assertSame(0, Chars::count(''));
    }

    public function testRand(): void
    {
        $this->assertSame('A', Chars::rand('A'));
        $this->assertSame('AAAAA', Chars::rand('A', 5));
    }

    public function testFirst(): void
    {
        $this->assertSame('H', Chars::first('Hello'));
        $this->assertSame('He', Chars::first('Hello', 2));
    }

    public function testSecond(): void
    {
        $this->assertSame('e', Chars::second('Hello'));
    }

    public function testThird(): void
    {
        $this->assertSame('l', Chars::third('Hello'));
    }

    public function testFourth(): void
    {
        $this->assertSame('l', Chars::fourth('Hello'));
    }

    public function testLast(): void
    {
        $this->assertSame('o', Chars::last('Hello'));
        $this->assertSame('lo', Chars::last('Hello', 2));
    }

    public function testSwap(): void
    {
        $this->assertSame('eHllo', Chars::swap('Hello', '1', '2'));
    }

    public function testRemoveFirst(): void
    {
        $this->assertSame('ello', Chars::removeFirst('Hello'));
        $this->assertSame('llo', Chars::removeFirst('Hello', 2));
        $this->assertSame('el', Chars::removeFirst('Hello', 1, 2));
    }

    public function testRemoveLast(): void
    {
        $this->assertSame('Hell', Chars::removeLast('Hello'));
        $this->assertSame('Hel', Chars::removeLast('Hello', 2));
    }

    public function testPopFirst(): void
    {
        $string = 'Hello';

        $this->assertSame('He', Chars::popFirst($string, 2));
        $this->assertSame('llo', $string);
    }

    public function testPopLast(): void
    {
        $string = 'Hello';

        $this->assertSame('lo', Chars::popLast($string, 2));
        $this->assertSame('Hel', $string);
    }

    public function testCountUnique(): void
    {
        $this->assertSame(4, Chars::countUnique('Hello'));
        $this->assertSame(1, Chars::countUnique('aaa'));
    }

    public function testUnique(): void
    {
        $this->assertSame(['H', 'e', 'l', 'o'], Chars::unique('Hello'));
    }

    public function testFrequency(): void
    {
        $this->assertSame(['H' => 1, 'e' => 1, 'l' => 2, 'o' => 1], Chars::frequency('Hello'));
    }

    public function testIsNull(): void
    {
        $this->assertTrue(Chars::isNull("\0"));

        $this->assertFalse(Chars::isNull('A'));
        $this->assertFalse(Chars::isNull('0'));
    }

    public function testIsPrintable(): void
    {
        $this->assertTrue(Chars::isPrintable('A'));
        $this->assertTrue(Chars::isPrintable(' '));

        $this->assertFalse(Chars::isPrintable("\0"));
        $this->assertFalse(Chars::isPrintable("\n"));
    }

    public function testIsUTF8(): void
    {
        $this->assertTrue(Chars::isUTF8('A'));
        $this->assertTrue(Chars::isUTF8('π'));
    }

    public function testToHex(): void
    {
        $this->assertSame('41', Chars::toHex('A'));
        $this->assertSame('4142', Chars::toHex('AB'));
    }
}

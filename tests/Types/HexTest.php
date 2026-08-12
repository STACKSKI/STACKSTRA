<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Hex;

#[CoversClass(Hex::class)]
class HexTest extends TestCase
{
    public function testToInt(): void
    {
        $this->assertSame(0, Hex::toInt('0'));
        $this->assertSame(16, Hex::toInt('10'));
        $this->assertSame(26, Hex::toInt('1a'));
        $this->assertSame(255, Hex::toInt('ff'));
    }

    public function testToBin(): void
    {
        $this->assertSame('A', Hex::toBin('41'));
        $this->assertSame('Hello', Hex::toBin('48656c6c6f'));
    }

    public function testIsPrintable(): void
    {
        $this->assertTrue(Hex::isPrintable('20'));  // 0x20 = 32  = ' '  (first printable)
        $this->assertTrue(Hex::isPrintable('41'));  // 0x41 = 65  = 'A'
        $this->assertTrue(Hex::isPrintable('7d'));  // 0x7d = 125 = '}'
        $this->assertTrue(Hex::isPrintable('7e'));  // 0x7e = 126 = '~'  (last printable)

        $this->assertFalse(Hex::isPrintable('1f')); // 0x1f = 31  (control, just below range)
        $this->assertFalse(Hex::isPrintable('7f')); // 0x7f = 127 (DEL, just above range)
        $this->assertFalse(Hex::isPrintable('ff')); // 0xff = 255
    }
}

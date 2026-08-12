<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\ASCII;

#[CoversClass(ASCII::class)]
class ASCIITest extends TestCase
{
    public function testIsPrintableIndex(): void
    {
        $this->assertTrue(ASCII::isPrintableIndex(32));
        $this->assertTrue(ASCII::isPrintableIndex(65));
        $this->assertTrue(ASCII::isPrintableIndex(126));

        $this->assertFalse(ASCII::isPrintableIndex(31));
        $this->assertFalse(ASCII::isPrintableIndex(127));
    }

    public function testIsPrintableChar(): void
    {
        $this->assertTrue(ASCII::isPrintableChar('A'));
        $this->assertTrue(ASCII::isPrintableChar(' '));

        $this->assertFalse(ASCII::isPrintableChar("\n"));
        $this->assertFalse(ASCII::isPrintableChar("\x7f"));
    }

    public function testIsPrintableHex(): void
    {
        $this->assertTrue(ASCII::isPrintableHex('20'));
        $this->assertTrue(ASCII::isPrintableHex('41'));

        $this->assertFalse(ASCII::isPrintableHex('1f'));
        $this->assertFalse(ASCII::isPrintableHex('7f'));
    }

    public function testIndexToChar(): void
    {
        $this->assertSame('A', ASCII::indexToChar(65));
        $this->assertSame('a', ASCII::indexToChar(97));
    }

    public function testIndexToHex(): void
    {
        $this->assertSame('41', ASCII::indexToHex(65));
        $this->assertSame('ff', ASCII::indexToHex(255));
    }

    public function testCharToIndex(): void
    {
        $this->assertSame(65, ASCII::charToIndex('A'));
    }

    public function testCharToHex(): void
    {
        $this->assertSame('41', ASCII::charToHex('A'));
        $this->assertSame('4142', ASCII::charToHex('AB'));
    }

    public function testHexToIndex(): void
    {
        $this->assertSame(65, ASCII::hexToIndex('41'));
    }

    public function testHexToChar(): void
    {
        $this->assertSame('A', ASCII::hexToChar('41'));
    }
}

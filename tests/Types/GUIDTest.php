<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\GUID;

#[CoversClass(GUID::class)]
class GUIDTest extends TestCase
{
    public function testToBin(): void
    {
        $this->assertSame('AB', GUID::toBin('41-42'));
        $this->assertSame('Hello', GUID::toBin('4865-6c6c-6f'));
    }

    public function testToHex(): void
    {
        $this->assertSame('550e8400e29b41d4a716446655440000', GUID::toHex('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertSame('nodashes', GUID::toHex('nodashes'));
    }
}

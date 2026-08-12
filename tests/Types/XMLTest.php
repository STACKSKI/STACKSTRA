<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\XML;

#[CoversClass(XML::class)]
class XMLTest extends TestCase
{
    public function testToJSON(): void
    {
        $this->assertSame('{"a":"1","b":"x"}', XML::toJSON('<root><a>1</a><b>x</b></root>'));

        $this->assertNull(XML::toJSON('<bad'));
    }

    public function testToObject(): void
    {
        $this->assertEquals((object) ['a' => '1'], XML::toObject('<root><a>1</a></root>'));

        $this->assertNull(XML::toObject('<bad'));
    }

    public function testToArray(): void
    {
        $this->assertSame(['a' => '1', 'b' => 'x'], XML::toArray('<root><a>1</a><b>x</b></root>'));

        $this->assertNull(XML::toArray('<bad'));
    }
}

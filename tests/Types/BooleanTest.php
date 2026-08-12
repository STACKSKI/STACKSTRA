<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Boolean;

#[CoversClass(Boolean::class)]
class BooleanTest extends TestCase
{
    public function testRand(): void
    {
        $this->assertIsBool(Boolean::rand());
    }

    public function testToString(): void
    {
        $this->assertSame('true', Boolean::toString(true));
        $this->assertSame('true', Boolean::toString(1));
        $this->assertSame('true', Boolean::toString('x'));

        $this->assertSame('false', Boolean::toString(false));
        $this->assertSame('false', Boolean::toString(0));
        $this->assertSame('false', Boolean::toString(''));
        $this->assertSame('false', Boolean::toString(null));
    }
}

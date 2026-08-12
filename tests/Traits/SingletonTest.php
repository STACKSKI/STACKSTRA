<?php

namespace Stackstra\Tests\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use ReflectionClass;
use Stackstra\Etc\Timer;
use Stackstra\Traits\Singleton;

#[CoversClass(Singleton::class)]
class SingletonTest extends TestCase
{
    public function testInstance(): void
    {
        // reset the shared static instance so this test is independent of others
        $prop = (new ReflectionClass(Timer::class))->getProperty('instance');
        $prop->setValue(null, null);

        $first = Timer::instance();

        $this->assertInstanceOf(Timer::class, $first);

        // subsequent calls return the same shared instance, ignoring new arguments
        $second = Timer::instance(false);

        $this->assertSame($first, $second);
    }

    public function testMake(): void
    {
        $a = Timer::make();
        $b = Timer::make();

        // every call produces a brand new, distinct instance
        $this->assertNotSame($a, $b);

        // optional constructor argument is forwarded
        $noAutostart = Timer::make(false);

        $this->assertInstanceOf(Timer::class, $noAutostart);
    }
}

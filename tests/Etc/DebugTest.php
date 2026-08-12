<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Stackstra\Etc\Debug;
use Stackstra\Tests\TestCase;

#[CoversClass(Debug::class)]
class DebugTest extends TestCase
{
    protected function tearDown(): void
    {
        // reset the cached static state so tests don't leak into each other
        $prop = (new ReflectionClass(Debug::class))->getProperty('is_enabled');
        $prop->setValue(null, null);
    }

    public function testEnable(): void
    {
        Debug::enable();

        $this->assertTrue(Debug::isEnabled());
    }

    public function testDisable(): void
    {
        Debug::disable();

        $this->assertFalse(Debug::isEnabled());
    }

    public function testIsEnabled(): void
    {
        // unset explicitly, falls back to the APP_DEBUG environment variable (unset here: false)
        putenv('APP_DEBUG');

        $this->assertFalse(Debug::isEnabled());

        // once resolved once, the cached value is returned regardless of a later env change
        putenv('APP_DEBUG=true');

        $this->assertFalse(Debug::isEnabled());

        // fresh state, env var set to true before the first resolution: resolves to true
        $this->tearDown();
        putenv('APP_DEBUG=true');

        $this->assertTrue(Debug::isEnabled());

        putenv('APP_DEBUG');
    }
}

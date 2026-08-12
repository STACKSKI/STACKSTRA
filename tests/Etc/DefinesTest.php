<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\Defines;

#[CoversClass(Defines::class)]
class DefinesTest extends TestCase
{
    public function testGet(): void
    {
        $constants = Defines::get();

        $this->assertArrayHasKey('PHP_VERSION', $constants);
    }

    public function testStartsWith(): void
    {
        $constants = Defines::startsWith('PHP_VERSION');

        $this->assertArrayHasKey('PHP_VERSION', $constants);
        $this->assertArrayNotHasKey('E_ERROR', $constants);
    }

    public function testEndsWith(): void
    {
        $constants = Defines::endsWith('_VERSION');

        $this->assertArrayHasKey('PHP_VERSION', $constants);
    }

    public function testSet(): void
    {
        Defines::set('STACKSTRA_TEST_DEFINE', 42);

        $this->assertTrue(defined('STACKSTRA_TEST_DEFINE'));
        $this->assertSame(42, STACKSTRA_TEST_DEFINE);
    }
}

<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Stackstra\Etc\Headers;
use Stackstra\Etc\Reflection;

#[CoversClass(Reflection::class)]
class ReflectionTest extends TestCase
{
    public function testInstance(): void
    {
        $this->assertInstanceOf(ReflectionClass::class, Reflection::instance(Headers::class));
    }

    public function testGetConstants(): void
    {
        $constants = Reflection::getConstants(Headers::class);

        $this->assertSame(200, $constants['CODE_SUCCESS']);
    }

    public function testGetConstantsPublic(): void
    {
        $constants = Reflection::getConstantsPublic(Headers::class);

        $this->assertSame(200, $constants['CODE_SUCCESS']);
    }

    public function testGetConstantsPublicStartWith(): void
    {
        $constants = Reflection::getConstantsPublicStartWith(Headers::class, 'CODE_');

        $this->assertCount(7, $constants);
        $this->assertSame(200, $constants['CODE_SUCCESS']);
        $this->assertArrayNotHasKey('METHOD_GET', $constants);
    }

    public function testGetMethods(): void
    {
        $methods = Reflection::getMethods(Headers::class);

        $this->assertContainsOnlyInstancesOf(ReflectionMethod::class, $methods);
        $this->assertNotEmpty($methods);
    }

    public function testGetClassShortName(): void
    {
        $this->assertSame('Headers', Reflection::getClassShortName(Headers::class));
    }
}

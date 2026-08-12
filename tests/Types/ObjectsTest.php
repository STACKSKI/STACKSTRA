<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Objects;

#[CoversClass(Objects::class)]
class ObjectsTest extends TestCase
{
    public function testToArray(): void
    {
        $object = (object) ['a' => 1, 'b' => (object) ['c' => 2], 'd' => [3, 4]];

        $this->assertSame(['a' => 1, 'b' => ['c' => 2], 'd' => [3, 4]], Objects::toArray($object));

        $this->assertNull(Objects::toArray(5));
    }

    public function testGet(): void
    {
        $object = (object) ['x' => 5];

        $this->assertSame(5, Objects::get($object, 'x'));
        $this->assertSame('def', Objects::get($object, 'y', 'def'));
    }

    public function testProperties(): void
    {
        $this->assertSame(['a' => 1, 'b' => 2], Objects::properties((object) ['a' => 1, 'b' => 2]));
    }

    public function testHasProperty(): void
    {
        $object = (object) ['a' => 1];

        $this->assertTrue(Objects::hasProperty($object, 'a'));

        $this->assertFalse(Objects::hasProperty($object, 'b'));
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(Objects::isEmpty(new \stdClass()));

        $this->assertFalse(Objects::isEmpty((object) ['a' => 1]));
    }
}

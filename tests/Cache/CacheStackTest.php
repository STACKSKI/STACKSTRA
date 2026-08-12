<?php

namespace Stackstra\Tests\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Cache\CacheStack;
use Stackstra\Tests\TestCase;

#[CoversClass(CacheStack::class)]
class CacheStackTest extends TestCase
{
    public function testConstruct(): void
    {
        // no limit: unbounded
        $stack = new CacheStack();
        $this->assertFalse($stack->isFull());

        // explicit limit
        $stack = new CacheStack(1);
        $stack->add('a');
        $this->assertTrue($stack->isFull());
    }

    public function testReset(): void
    {
        $stack = new CacheStack();
        $stack->add('a');

        $stack->reset();

        $this->assertTrue($stack->isEmpty());
    }

    public function testAdd(): void
    {
        $stack = new CacheStack(1);

        $this->assertTrue($stack->add('a'));

        // rejected once full
        $this->assertFalse($stack->add('b'));

        // ignore_limit=true bypasses the cap
        $this->assertTrue($stack->add('b', true));
        $this->assertSame(['a', 'b'], $stack->get());
    }

    public function testRemove(): void
    {
        $stack = new CacheStack();
        $stack->add('a');
        $stack->add('b');

        // LIFO: remove() pops from the top (end)
        $this->assertSame('b', $stack->remove());
        $this->assertSame('a', $stack->remove());

        // empty stack: null instead of throwing
        $this->assertNull($stack->remove());
    }

    public function testRemoveBulk(): void
    {
        $stack = new CacheStack();
        $stack->add('a');
        $stack->add('b');
        $stack->add('c');

        // removes n items in LIFO order
        $this->assertSame(['c', 'b'], $stack->removeBulk(2));

        // requesting more than remain pads the result with nulls
        $this->assertSame(['a', null], $stack->removeBulk(2));
    }

    public function testRemoveUntil(): void
    {
        $stack = new CacheStack();
        $stack->add('a');
        $stack->add('b');
        $stack->add('c');

        // removes items (from the top) until the target value is found
        $this->assertTrue($stack->removeUntil('b'));
        $this->assertSame(['a'], $stack->get());

        // target never found: everything is removed, returns false
        $stack = new CacheStack();
        $stack->add('a');
        $this->assertFalse($stack->removeUntil('z'));
        $this->assertTrue($stack->isEmpty());
    }

    public function testGet(): void
    {
        $stack = new CacheStack();
        $stack->add('a');
        $stack->add('b');

        $this->assertSame(['a', 'b'], $stack->get());
    }

    public function testGetFirst(): void
    {
        $stack = new CacheStack();
        $stack->add('a');
        $stack->add('b');
        $stack->add('c');

        // "first" on a stack means the top (most recently added): default n=1
        $this->assertSame('c', $stack->getFirst());

        // explicit n
        $this->assertSame(['b', 'c'], array_values($stack->getFirst(2)));
    }

    public function testGetLast(): void
    {
        $stack = new CacheStack();
        $stack->add('a');
        $stack->add('b');
        $stack->add('c');

        // "last" on a stack means the bottom (least recently added): default n=1
        $this->assertSame('a', $stack->getLast());

        // explicit n
        $this->assertSame(['a', 'b'], array_values($stack->getLast(2)));
    }

    public function testExist(): void
    {
        $stack = new CacheStack();
        $stack->add('a');

        $this->assertTrue($stack->exist('a'));
        $this->assertFalse($stack->exist('z'));
    }

    public function testIsTop(): void
    {
        $stack = new CacheStack();
        $stack->add('a');
        $stack->add('b');

        $this->assertTrue($stack->isTop('b'));
        $this->assertFalse($stack->isTop('a'));
    }

    public function testIsBottom(): void
    {
        $stack = new CacheStack();
        $stack->add('a');
        $stack->add('b');

        $this->assertTrue($stack->isBottom('a'));
        $this->assertFalse($stack->isBottom('b'));
    }

    public function testCount(): void
    {
        $stack = new CacheStack();
        $this->assertSame(0, $stack->count());

        $stack->add('a');
        $this->assertSame(1, $stack->count());
    }

    public function testIsEmpty(): void
    {
        $stack = new CacheStack();
        $this->assertTrue($stack->isEmpty());

        $stack->add('a');
        $this->assertFalse($stack->isEmpty());
    }

    public function testIsFull(): void
    {
        // no limit: never full
        $stack = new CacheStack();
        $stack->add('a');
        $this->assertFalse($stack->isFull());

        // at limit
        $stack = new CacheStack(1);
        $stack->add('a');
        $this->assertTrue($stack->isFull());
    }
}

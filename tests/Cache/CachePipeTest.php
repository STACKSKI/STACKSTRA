<?php

namespace Stackstra\Tests\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Cache\CachePipe;
use Stackstra\Tests\TestCase;

#[CoversClass(CachePipe::class)]
class CachePipeTest extends TestCase
{
    public function testConstruct(): void
    {
        // no limit: unbounded
        $pipe = new CachePipe();
        $this->assertFalse($pipe->isFull());

        // explicit limit
        $pipe = new CachePipe(1);
        $pipe->add('a');
        $this->assertTrue($pipe->isFull());
    }

    public function testAdd(): void
    {
        $pipe = new CachePipe(1);

        $this->assertTrue($pipe->add('a'));

        // rejected once full
        $this->assertFalse($pipe->add('b'));

        // force=true bypasses the limit
        $this->assertTrue($pipe->add('b', true));
        $this->assertSame(['a', 'b'], $pipe->get());
    }

    public function testTake(): void
    {
        $pipe = new CachePipe();
        $pipe->add('a');
        $pipe->add('b');

        // FIFO: take() removes from the front
        $this->assertSame('a', $pipe->take());
        $this->assertSame('b', $pipe->take());

        // empty pipe: null instead of throwing
        $this->assertNull($pipe->take());
    }

    public function testGet(): void
    {
        $pipe = new CachePipe();
        $pipe->add('a');
        $pipe->add('b');

        $this->assertSame(['a', 'b'], $pipe->get());
    }

    public function testGetFirst(): void
    {
        $pipe = new CachePipe();
        $pipe->add('a');
        $pipe->add('b');
        $pipe->add('c');

        // default n=1: a single scalar
        $this->assertSame('a', $pipe->getFirst());

        // explicit n
        $this->assertSame(['a', 'b'], array_values($pipe->getFirst(2)));
    }

    public function testGetLast(): void
    {
        $pipe = new CachePipe();
        $pipe->add('a');
        $pipe->add('b');
        $pipe->add('c');

        // default n=1: a single scalar
        $this->assertSame('c', $pipe->getLast());

        // explicit n
        $this->assertSame(['b', 'c'], array_values($pipe->getLast(2)));
    }

    public function testIsFirst(): void
    {
        $pipe = new CachePipe();
        $pipe->add('a');
        $pipe->add('b');

        // loose comparison
        $this->assertTrue($pipe->isFirst('a'));
        $this->assertFalse($pipe->isFirst('b'));
    }

    public function testIsLast(): void
    {
        $pipe = new CachePipe();
        $pipe->add('a');
        $pipe->add('b');

        $this->assertTrue($pipe->isLast('b'));
        $this->assertFalse($pipe->isLast('a'));
    }

    public function testCount(): void
    {
        $pipe = new CachePipe();
        $this->assertSame(0, $pipe->count());

        $pipe->add('a');
        $this->assertSame(1, $pipe->count());
    }

    public function testIsEmpty(): void
    {
        $pipe = new CachePipe();
        $this->assertTrue($pipe->isEmpty());

        $pipe->add('a');
        $this->assertFalse($pipe->isEmpty());
    }

    public function testIsFull(): void
    {
        // no limit: never full
        $pipe = new CachePipe();
        $pipe->add('a');
        $this->assertFalse($pipe->isFull());

        // at limit
        $pipe = new CachePipe(1);
        $pipe->add('a');
        $this->assertTrue($pipe->isFull());
    }
}

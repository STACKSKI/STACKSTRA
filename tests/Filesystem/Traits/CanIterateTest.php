<?php

namespace Stackstra\Tests\Filesystem\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use Iterator;
use Stackstra\Filesystem\Traits\CanIterate;
use Stackstra\Tests\TestCase;

#[CoversClass(CanIterate::class)]
class CanIterateTest extends TestCase
{
    private function subject(array $items): Iterator
    {
        return new class ($items) implements Iterator {
            use CanIterate;

            public function __construct(private array $items) {}
            protected function directories(): array { return $this->items; }
        };
    }

    public function testRewind(): void
    {
        $subject = $this->subject(['a', 'b']);

        $subject->rewind();

        $this->assertSame('a', $subject->current());
    }

    public function testCurrent(): void
    {
        $subject = $this->subject(['a', 'b']);
        $subject->rewind();

        $this->assertSame('a', $subject->current());
    }

    public function testKey(): void
    {
        $subject = $this->subject(['a', 'b']);
        $subject->rewind();

        $this->assertSame(0, $subject->key());

        $subject->next();
        $this->assertSame(1, $subject->key());
    }

    public function testNext(): void
    {
        $subject = $this->subject(['a', 'b']);
        $subject->rewind();

        $subject->next();

        $this->assertSame('b', $subject->current());
    }

    public function testValid(): void
    {
        $subject = $this->subject(['a']);
        $subject->rewind();

        $this->assertTrue($subject->valid());

        $subject->next();
        $this->assertFalse($subject->valid());
    }

    public function testFullIteration(): void
    {
        $subject = $this->subject(['a', 'b', 'c']);

        $seen = [];

        foreach ($subject as $key => $value)
        {
            $seen[$key] = $value;
        }

        $this->assertSame(['a', 'b', 'c'], $seen);
    }

    public function testEmptyIteration(): void
    {
        $subject = $this->subject([]);

        $subject->rewind();

        $this->assertFalse($subject->valid());
    }
}

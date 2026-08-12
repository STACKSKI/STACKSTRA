<?php

namespace Stackstra\Tests\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use Stackstra\Cache\Cache;
use Stackstra\Tests\TestCase;

#[CoversClass(Cache::class)]
class CacheTest extends TestCase
{
    public function testConstructAndMake(): void
    {
        // default: empty items, no limit
        $cache = new Cache();
        $this->assertSame([], $cache->get());
        $this->assertFalse($cache->isFull());

        // items argument seeds the cache
        $cache = new Cache(['a', 'b']);
        $this->assertSame(['a', 'b'], $cache->get());

        // limit argument caps how many items can be pushed
        $cache = new Cache(['a'], 1);
        $this->assertTrue($cache->isFull());

        // make() behaves identically, both arguments optional
        $this->assertSame([], Cache::make()->get());
        $this->assertSame(['a', 'b'], Cache::make(['a', 'b'])->get());
        $this->assertTrue(Cache::make(['a'], 1)->isFull());
    }

    public function testPush(): void
    {
        $cache = new Cache([], 1);

        // first push succeeds, filling the cache to its limit
        $this->assertTrue($cache->push('a'));
        $this->assertSame(['a'], $cache->get());

        // second push is rejected once full
        $this->assertFalse($cache->push('b'));
        $this->assertSame(['a'], $cache->get());

        // ignore_limit=true bypasses the cap
        $this->assertTrue($cache->push('b', true));
        $this->assertSame(['a', 'b'], $cache->get());
    }

    public function testPushBulk(): void
    {
        $cache = new Cache([], 2);

        // all values fit: overall result is true
        $this->assertTrue($cache->pushBulk(['a', 'b']));
        $this->assertSame(['a', 'b'], $cache->get());

        // limit reached: pushBulk returns false and only pushes what fits
        $cache = new Cache([], 1);
        $this->assertFalse($cache->pushBulk(['a', 'b']));
        $this->assertSame(['a'], $cache->get());

        // ignore_limit=true forces every value in regardless of the cap
        $cache = new Cache([], 1);
        $this->assertTrue($cache->pushBulk(['a', 'b'], true));
        $this->assertSame(['a', 'b'], $cache->get());
    }

    public function testPop(): void
    {
        $cache = new Cache(['a', 'b']);

        $this->assertSame('b', $cache->pop());
        $this->assertSame(['a'], $cache->get());

        $cache->pop();

        // popping an empty cache returns null instead of throwing
        $this->assertNull($cache->pop());
    }

    public function testUnshift(): void
    {
        $cache = new Cache(['b'], 2);

        $this->assertTrue($cache->unshift('a'));
        $this->assertSame(['a', 'b'], $cache->get());

        // rejected once the cache is full
        $this->assertFalse($cache->unshift('z'));
        $this->assertSame(['a', 'b'], $cache->get());
    }

    public function testShift(): void
    {
        $cache = new Cache(['a', 'b']);

        $this->assertSame('a', $cache->shift());
        $this->assertSame(['b'], $cache->get());

        $cache->shift();

        // shifting an empty cache returns null instead of throwing
        $this->assertNull($cache->shift());
    }

    public function testUnique(): void
    {
        $cache = new Cache(['a', 'b', 'a', 'c', 'b']);

        $cache->unique();

        $this->assertSame(['a', 'b', 'c'], array_values($cache->get()));
    }

    public function testRemoveValue(): void
    {
        $cache = new Cache(['a', 'b', 'c']);

        $cache->removeValue('b');
        $this->assertSame(['a', 'c'], array_values($cache->get()));

        // removing a value that doesn't exist is a no-op
        $cache->removeValue('z');
        $this->assertSame(['a', 'c'], array_values($cache->get()));
    }

    public function testRemove(): void
    {
        $cache = new Cache(['a' => 1, 'b' => 2]);

        $cache->remove('a');
        $this->assertSame(['b' => 2], $cache->get());

        // removing a non-existent key is a no-op
        $cache->remove('z');
        $this->assertSame(['b' => 2], $cache->get());
    }

    public function testReset(): void
    {
        $cache = new Cache(['a', 'b']);

        $result = $cache->reset();

        $this->assertSame([], $cache->get());
        $this->assertSame($cache, $result); // fluent return
    }

    public function testGet(): void
    {
        $cache = new Cache(['a' => 1, 'b' => 2]);

        // no arguments: the whole items array
        $this->assertSame(['a' => 1, 'b' => 2], $cache->get());

        // key present
        $this->assertSame(1, $cache->get('a'));

        // key missing, default provided
        $this->assertSame('fallback', $cache->get('z', 'fallback'));

        // key missing, default omitted -> null
        $this->assertNull($cache->get('z'));
    }

    public function testGetOrFail(): void
    {
        $cache = new Cache(['a' => 1]);

        $this->assertSame(1, $cache->getOrFail('a'));

        $this->expectException(RuntimeException::class);
        $cache->getOrFail('missing');
    }

    public function testHit(): void
    {
        $cache = new Cache();

        // key doesn't exist: it's created from the (invoked) default and returned
        $this->assertSame('created', $cache->hit('a', fn () => 'created'));
        $this->assertSame('created', $cache->get('a'));

        // key already exists: default is ignored entirely
        $this->assertSame('created', $cache->hit('a', fn () => 'ignored'));
    }

    public function testGetFirst(): void
    {
        $cache = new Cache(['a', 'b', 'c', 'd']);

        // default n=1, offset=0: a single scalar, not wrapped in an array
        $this->assertSame('a', $cache->getFirst());

        // explicit n
        $this->assertSame(['a', 'b'], array_values($cache->getFirst(2)));

        // explicit n and offset
        $this->assertSame(['b', 'c'], array_values($cache->getFirst(2, 1)));
    }

    public function testGetLast(): void
    {
        $cache = new Cache(['a', 'b', 'c', 'd']);

        // default n=1, offset=0: a single scalar, not wrapped in an array
        $this->assertSame('d', $cache->getLast());

        // explicit n
        $this->assertSame(['c', 'd'], array_values($cache->getLast(2)));

        // explicit n and offset
        $this->assertSame(['b', 'c'], array_values($cache->getLast(2, 1)));
    }

    public function testCount(): void
    {
        $this->assertSame(0, (new Cache())->count());
        $this->assertSame(3, (new Cache(['a', 'b', 'c']))->count());
    }

    public function testSet(): void
    {
        $cache = new Cache();

        $cache->set('a', 1);

        $this->assertSame(1, $cache->get('a'));

        // overwrites an existing key
        $cache->set('a', 2);
        $this->assertSame(2, $cache->get('a'));
    }

    public function testSetIfNotExist(): void
    {
        $cache = new Cache();

        // key doesn't exist: value() is invoked and stored
        $this->assertSame('created', $cache->setIfNotExist('a', fn () => 'created'));
        $this->assertSame('created', $cache->get('a'));

        // key exists: the provided value is ignored entirely
        $this->assertSame('created', $cache->setIfNotExist('a', fn () => 'ignored'));
    }

    public function testSetBulk(): void
    {
        $cache = new Cache(['a' => 1, 'b' => 2]);

        $cache->setBulk(['b' => 20, 'c' => 3]);

        // array union: values from the argument win for keys present in both
        $this->assertSame(['b' => 20, 'c' => 3, 'a' => 1], $cache->get());
    }

    public function testCopy(): void
    {
        $cache = new Cache(['a' => 1]);

        $cache->copy('a', 'b');

        $this->assertSame(1, $cache->get('b'));
    }

    public function testCopyIfNotExist(): void
    {
        $cache = new Cache(['a' => 1, 'b' => 2]);

        // destination already exists: no-op
        $cache->copyIfNotExist('a', 'b');
        $this->assertSame(2, $cache->get('b'));

        // destination missing: copied
        $cache->copyIfNotExist('a', 'c');
        $this->assertSame(1, $cache->get('c'));

        // source missing: no-op, destination stays unset
        $cache->copyIfNotExist('missing', 'd');
        $this->assertFalse($cache->isExist('d'));
    }

    public function testIsExistValue(): void
    {
        $cache = new Cache(['a', 'b']);

        $this->assertTrue($cache->isExistValue('a'));
        $this->assertFalse($cache->isExistValue('z'));
    }

    public function testIsExist(): void
    {
        $cache = new Cache(['a' => 1]);

        $this->assertTrue($cache->isExist('a'));
        $this->assertFalse($cache->isExist('z'));
    }

    public function testIsExistAll(): void
    {
        $cache = new Cache(['a' => 1, 'b' => 2]);

        $this->assertTrue($cache->isExistAll(['a', 'b']));
        $this->assertFalse($cache->isExistAll(['a', 'z']));

        // an empty key list is vacuously true
        $this->assertTrue($cache->isExistAll([]));
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue((new Cache())->isEmpty());
        $this->assertFalse((new Cache(['a']))->isEmpty());
    }

    public function testIsFull(): void
    {
        // no limit: never full
        $this->assertFalse((new Cache(['a', 'b', 'c']))->isFull());

        // below limit
        $this->assertFalse((new Cache(['a'], 2))->isFull());

        // at limit
        $this->assertTrue((new Cache(['a', 'b'], 2))->isFull());
    }
}

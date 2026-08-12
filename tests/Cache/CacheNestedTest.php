<?php

namespace Stackstra\Tests\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Cache\CacheNested;
use Stackstra\Tests\TestCase;

#[CoversClass(CacheNested::class)]
class CacheNestedTest extends TestCase
{
    public function testConstruct(): void
    {
        // no argument: starts empty
        $this->assertSame([], (new CacheNested())->get());

        // explicit array argument seeds the items, and is bound by reference
        $seed  = ['a' => 1];
        $cache = new CacheNested($seed);
        $this->assertSame(['a' => 1], $cache->get());

        $cache->set('a', 2);
        $this->assertSame(2, $seed['a']); // reference is shared with the caller's variable
    }

    public function testPointer(): void
    {
        $cache_data = ['a' => ['b' => 1]];
        $cache      = new CacheNested($cache_data);

        // scalar index
        $this->assertSame(1, $cache->pointer(['a', 'b']));
        $this->assertTrue($cache->isPointerValid());

        // single non-array index also works, wrapped by (array) cast
        $this->assertSame(['b' => 1], $cache->pointer('a'));
        $this->assertTrue($cache->isPointerValid());

        // missing path: default is returned (null when omitted) and the pointer is marked invalid
        $this->assertNull($cache->pointer(['a', 'z']));
        $this->assertFalse($cache->isPointerValid());

        $this->assertSame('fallback', $cache->pointer(['a', 'z'], 'fallback'));
        $this->assertFalse($cache->isPointerValid());

        // walking a scalar value as if it were a nested array falls back to the default instead of crashing
        $scalar_data  = ['a' => 5];
        $cache_scalar = new CacheNested($scalar_data);
        $this->assertSame('fallback', $cache_scalar->pointer(['a', 'b'], 'fallback'));
        $this->assertFalse($cache_scalar->isPointerValid());
    }

    public function testGet(): void
    {
        $cache_data = ['a' => ['b' => 1]];
        $cache = new CacheNested($cache_data);

        // no arguments: entire items array
        $this->assertSame(['a' => ['b' => 1]], $cache->get());

        // existing nested path
        $this->assertSame(1, $cache->get(['a', 'b']));

        // missing path, default omitted -> null
        $this->assertNull($cache->get(['a', 'z']));

        // missing path, default provided
        $this->assertSame('fallback', $cache->get(['a', 'z'], 'fallback'));
    }

    public function testGetFlat(): void
    {
        $cache_data = ['a' => [1, [2, 3]], 'b' => 4];
        $cache = new CacheNested($cache_data);

        $this->assertSame([1, 2, 3, 4], $cache->getFlat());
    }

    public function testGetOrUpdate(): void
    {
        $cache_data = ['a' => 1];
        $cache = new CacheNested($cache_data);

        // existing index: the callback is invoked and its result overwrites the current value
        $result = $cache->getOrUpdate('a', fn ($x, $y) => $x + $y, [2, 3]);
        $this->assertSame(5, $result);
        $this->assertSame(5, $cache->get('a'));

        // missing index: the callback is never invoked, the (invalid-pointer) default is returned as-is
        $this->assertNull($cache->getOrUpdate('missing', fn () => 'ignored'));
        $this->assertFalse($cache->isExist('missing'));
    }

    public function testSet(): void
    {
        $cache = new CacheNested();

        $cache->set(['a', 'b'], 1);

        $this->assertSame(1, $cache->get(['a', 'b']));
    }

    public function testCreate(): void
    {
        $cache = new CacheNested();

        // with explicit value
        $cache->create('a', 1);
        $this->assertSame(1, $cache->get('a'));

        // value omitted: defaults to null
        $cache->create('b');
        $this->assertNull($cache->get('b'));
        $this->assertTrue($cache->isExist('b'));
    }

    public function testRemove(): void
    {
        $cache_data = ['a' => ['b' => ['c' => 1]]];
        $cache = new CacheNested($cache_data);

        // removing a missing index returns false
        $this->assertFalse($cache->remove('missing'));

        // single-level removal
        $this->assertTrue($cache->remove(['a', 'b', 'c']));
        $this->assertFalse($cache->isExist(['a', 'b', 'c']));

        // scalar (non-array) index also works
        $cache2_data = ['x' => 1];
        $cache2 = new CacheNested($cache2_data);
        $this->assertTrue($cache2->remove('x'));
        $this->assertFalse($cache2->isExist('x'));
    }

    public function testReset(): void
    {
        $cache_data = ['a' => 1];
        $cache = new CacheNested($cache_data);

        $cache->reset();

        $this->assertSame([], $cache->get());
    }

    public function testIsPointerValid(): void
    {
        $cache_data = ['a' => 1];
        $cache = new CacheNested($cache_data);

        $cache->pointer('a');
        $this->assertTrue($cache->isPointerValid());

        $cache->pointer('missing');
        $this->assertFalse($cache->isPointerValid());
    }

    public function testIsExist(): void
    {
        $cache_data = ['a' => ['b' => 1]];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->isExist(['a', 'b']));
        $this->assertFalse($cache->isExist(['a', 'z']));
    }

    public function testIsArray(): void
    {
        $cache_data = ['a' => ['b' => 1], 'c' => 1];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->isArray('a'));
        $this->assertFalse($cache->isArray('c'));

        // missing path: false
        $this->assertFalse($cache->isArray('missing'));
    }

    public function testIsNull(): void
    {
        $cache_data = ['a' => null, 'b' => 1];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->isNull('a'));
        $this->assertFalse($cache->isNull('b'));

        // missing path: false (not the same as "is null")
        $this->assertFalse($cache->isNull('missing'));
    }

    public function testIsEmpty(): void
    {
        $cache_data = ['a' => [], 'b' => [1], 'c' => 0];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->isEmpty('a'));
        $this->assertFalse($cache->isEmpty('b'));
        $this->assertTrue($cache->isEmpty('c'));

        // missing path: considered empty
        $this->assertTrue($cache->isEmpty('missing'));
    }

    public function testArrayReset(): void
    {
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->arrayReset('a'));
        $this->assertSame([], $cache->get('a'));

        // missing path: false, nothing created
        $this->assertFalse($cache->arrayReset('missing'));
    }

    public function testArrayIsKeyExist(): void
    {
        $cache_data = ['a' => ['b' => 1]];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->arrayIsKeyExist('a', 'b'));
        $this->assertFalse($cache->arrayIsKeyExist('a', 'z'));

        // missing path itself
        $this->assertFalse($cache->arrayIsKeyExist('missing', 'b'));
    }

    public function testArrayIsValueExist(): void
    {
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->arrayIsValueExist('a', 2));
        $this->assertFalse($cache->arrayIsValueExist('a', 9));

        // missing path
        $this->assertFalse($cache->arrayIsValueExist('missing', 2));
    }

    public function testArrayRemoveValues(): void
    {
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);

        // scalar value is cast to an array
        $removed = $cache->arrayRemoveValues('a', 2);
        $this->assertSame(1, $removed);
        $this->assertSame([1, 3], array_values($cache->get('a')));

        // array of values
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);
        $removed = $cache->arrayRemoveValues('a', [1, 3]);
        $this->assertSame(2, $removed);

        // missing path: 0 removed
        $cache_data = [];
        $cache = new CacheNested($cache_data);
        $this->assertSame(0, $cache->arrayRemoveValues('missing', [1]));
    }

    public function testArrayRemoveKeys(): void
    {
        $cache_data = ['a' => ['x' => 1, 'y' => 2]];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->arrayRemoveKeys('a', ['x']));
        $this->assertSame(['y' => 2], $cache->get('a'));

        // missing path: false
        $this->assertFalse($cache->arrayRemoveKeys('missing', ['x']));

        // path exists but isn't an array: false
        $cache2_data = ['a' => 1];
        $cache2 = new CacheNested($cache2_data);
        $this->assertFalse($cache2->arrayRemoveKeys('a', ['x']));
    }

    public function testArrayKeepValues(): void
    {
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);

        $this->assertTrue($cache->arrayKeepValues('a', [2, 3]));
        $this->assertSame([2, 3], array_values($cache->get('a')));

        $this->assertFalse($cache->arrayKeepValues('missing', [1]));
    }

    public function testArrayKeepKeys(): void
    {
        $cache_data = ['a' => ['x' => 1, 'y' => 2]];
        $cache = new CacheNested($cache_data);

        // $keys is matched by its own array keys (array_intersect_key), not its values
        $this->assertTrue($cache->arrayKeepKeys('a', ['x' => null]));
        $this->assertSame(['x' => 1], $cache->get('a'));

        $this->assertFalse($cache->arrayKeepKeys('missing', ['x' => null]));
    }

    public function testArrayGet(): void
    {
        $cache_data = ['a' => ['x' => 1]];
        $cache = new CacheNested($cache_data);

        $this->assertSame(1, $cache->arrayGet('a', 'x'));

        // key missing, default omitted -> null
        $this->assertNull($cache->arrayGet('a', 'z'));

        // key missing, default provided
        $this->assertSame('fallback', $cache->arrayGet('a', 'z', 'fallback'));

        // path missing entirely: default is returned
        $this->assertSame('fallback', $cache->arrayGet('missing', 'x', 'fallback'));
    }

    public function testArrayIsEqual(): void
    {
        $cache_data = ['a' => '1'];
        $cache = new CacheNested($cache_data);

        // loose comparison by default
        $this->assertTrue($cache->arrayIsEqual('a', 1));

        // strict comparison
        $this->assertFalse($cache->arrayIsEqual('a', 1, true));
        $this->assertTrue($cache->arrayIsEqual('a', '1', true));

        // missing path: false
        $this->assertFalse($cache->arrayIsEqual('missing', 1));
    }

    public function testArrayPush(): void
    {
        $cache = new CacheNested();

        // path doesn't exist yet: it's created as an array first
        $cache->arrayPush('a', 1);
        $this->assertSame([1], $cache->get('a'));

        $cache->arrayPush('a', 2);
        $this->assertSame([1, 2], $cache->get('a'));
    }

    public function testArrayPushBulk(): void
    {
        $cache = new CacheNested();

        $cache->arrayPushBulk('a', [1, 2]);
        $this->assertSame([1, 2], $cache->get('a'));

        $cache->arrayPushBulk('a', [3]);
        $this->assertSame([1, 2, 3], $cache->get('a'));
    }

    public function testArrayCount(): void
    {
        $cache_data = ['a' => [1, 2, 3], 'b' => 1];
        $cache = new CacheNested($cache_data);

        $this->assertSame(3, $cache->arrayCount('a'));

        // not an array: 0
        $this->assertSame(0, $cache->arrayCount('b'));

        // missing path: 0
        $this->assertSame(0, $cache->arrayCount('missing'));
    }

    public function testArrayPop(): void
    {
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);

        $this->assertSame(3, $cache->arrayPop('a'));
        $this->assertSame([1, 2], $cache->get('a'));

        // missing path: null
        $this->assertNull($cache->arrayPop('missing'));
    }

    public function testArrayShift(): void
    {
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);

        $this->assertSame(1, $cache->arrayShift('a'));
        $this->assertSame([2, 3], array_values($cache->get('a')));

        $this->assertNull($cache->arrayShift('missing'));
    }

    public function testArrayUnshift(): void
    {
        $cache_data = ['a' => [2, 3]];
        $cache = new CacheNested($cache_data);

        $count = $cache->arrayUnshift('a', 1);
        $this->assertSame(3, $count);
        $this->assertSame([1, 2, 3], array_values($cache->get('a')));

        $this->assertNull($cache->arrayUnshift('missing', 1));
    }

    public function testArrayFirst(): void
    {
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);

        // default n=1
        $this->assertSame(1, $cache->arrayFirst('a'));

        // explicit n
        $this->assertSame([1, 2], array_values($cache->arrayFirst('a', 2)));

        // missing path: null
        $this->assertNull($cache->arrayFirst('missing'));
    }

    public function testArrayLast(): void
    {
        $cache_data = ['a' => [1, 2, 3]];
        $cache = new CacheNested($cache_data);

        // default n=1
        $this->assertSame(3, $cache->arrayLast('a'));

        // explicit n
        $this->assertSame([2, 3], array_values($cache->arrayLast('a', 2)));

        // missing path: null
        $this->assertNull($cache->arrayLast('missing'));
    }
}

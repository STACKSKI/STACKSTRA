<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Items;

#[CoversClass(Items::class)]
class ItemsTest extends TestCase
{
    public function testGet(): void
    {
        $this->assertSame(5, Items::get(['a' => ['b' => 5]], ['a', 'b']));
        $this->assertSame(1, Items::get(['a' => 1], 'a'));

        $this->assertSame('def', Items::get(['a' => 1], ['a', 'x'], 'def'));
    }

    public function testGetByKeys(): void
    {
        $this->assertSame(['a' => 1, 'c' => 3], Items::getByKeys(['a' => 1, 'b' => 2, 'c' => 3], ['a', 'c']));
    }

    public function testNth(): void
    {
        $this->assertSame('y', Items::nth(['x', 'y', 'z'], 2));

        $this->assertNull(Items::nth(['x'], 5));
    }

    public function testNthKey(): void
    {
        $this->assertSame('b', Items::nthKey(['a' => 1, 'b' => 2], 2));

        $this->assertNull(Items::nthKey(['a' => 1], 5));
    }

    public function testNthToLast(): void
    {
        $this->assertSame(4, Items::nthToLast([1, 2, 3, 4], 1));
        $this->assertSame(3, Items::nthToLast([1, 2, 3, 4], 2));

        $this->assertNull(Items::nthToLast([1, 2, 3, 4], 5));
    }

    public function testFirst(): void
    {
        $this->assertSame(10, Items::first([10, 20, 30]));
        $this->assertSame([10, 20], Items::first([10, 20, 30], 2));

        $this->assertNull(Items::first([]));
    }

    public function testSecond(): void
    {
        $this->assertSame(20, Items::second([10, 20, 30]));
    }

    public function testThird(): void
    {
        $this->assertSame(30, Items::third([10, 20, 30]));
    }

    public function testLast(): void
    {
        $this->assertSame(30, Items::last([10, 20, 30]));
        $this->assertSame([1 => 20, 2 => 30], Items::last([10, 20, 30], 2));

        $this->assertNull(Items::last([]));
    }

    public function testSecondToLast(): void
    {
        $this->assertSame(20, Items::secondToLast([10, 20, 30]));
    }

    public function testThirdToLast(): void
    {
        $this->assertSame(10, Items::thirdToLast([10, 20, 30]));
    }

    public function testReindex(): void
    {
        $this->assertSame(['a', 'b'], Items::reindex([5 => 'a', 9 => 'b']));
        $this->assertSame(['b', 'c'], Items::reindex(['a', 'b', 'c'], 1));
    }

    public function testKeyExist(): void
    {
        $this->assertTrue(Items::keyExist(['a' => 1], 'a'));

        $this->assertFalse(Items::keyExist(['a' => 1], 'x'));
    }

    public function testKeyFirst(): void
    {
        $this->assertSame('a', Items::keyFirst(['a' => 1, 'b' => 2]));
    }

    public function testKeyLast(): void
    {
        $this->assertSame('b', Items::keyLast(['a' => 1, 'b' => 2]));
    }

    public function testOnly(): void
    {
        $this->assertSame(['a' => 1, 'x' => 'd'], Items::only(['a' => 1, 'b' => 2], ['a', 'x'], 'd'));
    }

    public function testShift(): void
    {
        $array = [1, 2, 3];

        $this->assertSame(1, Items::shift($array));
        $this->assertSame([2, 3], $array);
    }

    public function testPop(): void
    {
        $array = [1, 2, 3];

        $this->assertSame(3, Items::pop($array));
        $this->assertSame([1, 2], $array);
    }

    public function testPush(): void
    {
        $array = [1, 2];

        $this->assertSame(3, Items::push($array, 3));
        $this->assertSame([1, 2, 3], $array);
    }

    public function testUnshift(): void
    {
        $array = [2, 3];

        $this->assertSame(3, Items::unshift($array, 1));
        $this->assertSame([1, 2, 3], $array);
    }

    public function testContains(): void
    {
        $this->assertTrue(Items::contains([1, 2, 3], 2));
        $this->assertTrue(Items::contains([1, 2, 3], [9, 2]));

        $this->assertFalse(Items::contains([1, 2, 3], 9));
    }

    public function testContainsAny(): void
    {
        $this->assertTrue(Items::containsAny([1, 2, 3], [9, 2]));

        $this->assertFalse(Items::containsAny([1, 2, 3], [8, 9]));
    }

    public function testContainsAll(): void
    {
        $this->assertTrue(Items::containsAll([1, 2, 3], [1, 2]));

        $this->assertFalse(Items::containsAll([1, 2, 3], [1, 9]));
    }

    public function testRand(): void
    {
        $this->assertSame([10, 20, 30], Items::rand([10, 20, 30], 3));
    }

    public function testRandLibc(): void
    {
        $this->assertSame([10, 20, 30], Items::randLibc([10, 20, 30], 3));
    }

    public function testSwap(): void
    {
        $positions = ['a', 'b', 'c'];
        $keyed     = ['x' => 1, 'y' => 2];

        $this->assertSame(['c', 'b', 'a'], Items::swap($positions, '1', '3'));
        $this->assertSame(['x' => 2, 'y' => 1], Items::swap($keyed, 'x', 'y'));
    }

    public function testTrim(): void
    {
        $this->assertSame(['a', 'b'], Items::trim(['  a  ', ' b ']));
    }

    public function testTrimKeys(): void
    {
        $this->assertSame(['a' => 1, 'b' => 2], Items::trimKeys([' a ' => 1, ' b ' => 2]));
    }

    public function testCount(): void
    {
        $this->assertSame(2, Items::count(['a', 'b'], ['a', 'b', 'b', 'a', 'a']));

        $this->assertSame(0, Items::count(['z'], ['a']));
    }

    public function testSum(): void
    {
        $this->assertSame(6.0, Items::sum([1, 2, 3]));
        $this->assertSame(5.0, Items::sum([['n' => 2], ['n' => 3]], 'n'));
    }

    public function testRemoveFirst(): void
    {
        $this->assertSame([3, 4], Items::removeFirst([1, 2, 3, 4], 2));
    }

    public function testRemoveLast(): void
    {
        $this->assertSame([1, 2], Items::removeLast([1, 2, 3, 4], 2));
    }

    public function testRemovePairs(): void
    {
        $this->assertSame(
            [2 => 'f', 3 => 'r', 5 => 'v', 6 => 'r', 7 => 'b', 8 => 't'],
            Items::removePairs(['a', 'b'], ['a', 'b', 'f', 'r', 'b', 'v', 'r', 'b', 't', 'a'])
        );
    }

    public function testRemoveEmptyStrings(): void
    {
        $this->assertSame(['a', 'b'], Items::removeEmptyStrings(['a', '', 'b', '']));
    }

    public function testRemoveNegative(): void
    {
        $this->assertSame([0 => 1, 2 => 3], Items::removeNegative([1, -2, 3, -4]));
    }

    public function testRemovePositive(): void
    {
        $this->assertSame([1 => -2], Items::removePositive([1, -2, 3]));
    }

    public function testRemoveNonNegative(): void
    {
        $this->assertSame([1 => -2], Items::removeNonNegative([1, -2, 0, 3]));
    }

    public function testRemoveNonPositive(): void
    {
        $this->assertSame([0 => 1, 3 => 3], Items::removeNonPositive([1, -2, 0, 3]));
    }

    public function testRemoveNull(): void
    {
        $this->assertSame([0 => 1, 2 => 2], Items::removeNull([1, null, 2, null]));
    }

    public function testRemoveValues(): void
    {
        $this->assertSame([0 => 1, 2 => 3], Items::removeValues([1, 2, 3, 4], [2, 4]));
    }

    public function testRemoveKeys(): void
    {
        $this->assertSame(['a' => 1, 'c' => 3], Items::removeKeys(['a' => 1, 'b' => 2, 'c' => 3], ['b']));
    }

    public function testKeepValues(): void
    {
        $this->assertSame([1 => 2, 3 => 4], Items::keepValues([1, 2, 3, 4], [2, 4]));
    }

    public function testKeepKeys(): void
    {
        $this->assertSame(['a' => 1], Items::keepKeys(['a' => 1, 'b' => 2], ['a' => 0]));
    }

    public function testKeys(): void
    {
        $this->assertSame(['a', 'b'], Items::keys(['a' => 1, 'b' => 2]));
    }

    public function testPermutation(): void
    {
        $this->assertSame([['A', 'B'], ['B', 'A']], Items::permutation(['A', 'B']));
    }

    public function testCombinations(): void
    {
        $this->assertSame([[3, 2], [4, 1]], Items::combinations(5, [1, 2, 3, 4]));

        $this->assertSame(5, array_sum(Items::combinations(5, [1, 2, 3, 4], true)));
    }

    public function testMerge(): void
    {
        $this->assertSame([1, 2, 3, 4], Items::merge([1, 2], [3, 4]));
        $this->assertSame([1, 5], Items::merge([1], 5));
    }

    public function testMergeRecursive(): void
    {
        $this->assertSame(['a' => [1, 2]], Items::mergeRecursive(['a' => [1]], ['a' => [2]]));
        $this->assertSame([[1, 2]], Items::mergeRecursive([1, 2]));
    }

    public function testCombineRecursive(): void
    {
        $this->assertSame(['a' => 2], Items::combineRecursive(['a' => 1], ['a' => 2]));
        $this->assertSame([[1, 2]], Items::combineRecursive([1, 2]));
    }

    public function testToObject(): void
    {
        $this->assertEquals((object) ['a' => 1, 'b' => (object) ['c' => 2]], Items::toObject(['a' => 1, 'b' => ['c' => 2]]));
        $this->assertEquals((object) ['a_b' => 1], Items::toObject(['a-b' => 1], true));
    }

    public function testToXML(): void
    {
        $this->assertSame('<root><a>1</a><b>2</b></root>', Items::toXML(['a' => 1, 'b' => 2], 'root'));
    }

    public function testToLowercase(): void
    {
        $this->assertSame(['abc', 'def'], Items::toLowercase(['ABC', 'DeF']));
    }

    public function testToInt(): void
    {
        $this->assertSame([1, 2, 3], Items::toInt(['1', '2', '3']));
    }

    public function testMaxLength(): void
    {
        $this->assertSame(3, Items::maxLength(['a', 'bbb', 'cc']));
    }

    public function testMaxLengthKeys(): void
    {
        $this->assertSame(3, Items::maxLengthKeys(['a' => 1, 'bbb' => 2]));
    }

    public function testMinLength(): void
    {
        $this->assertSame(1, Items::minLength(['a', 'bbb', 'cc']));
    }

    public function testMinLengthKeys(): void
    {
        $this->assertSame(1, Items::minLengthKeys(['a' => 1, 'bbb' => 2]));
    }

    public function testMax(): void
    {
        $this->assertSame(3, Items::max([3, 1, 2]));
        $this->assertSame(5, Items::max([['n' => 3], ['n' => 5]], 'n'));
    }

    public function testMin(): void
    {
        $this->assertSame(1, Items::min([3, 1, 2]));
    }

    public function testMaxInt(): void
    {
        $this->assertSame(20, Items::maxInt(['a', '10', '5x', '20']));
    }

    public function testMinInt(): void
    {
        $this->assertSame(5, Items::minInt(['a', '10', '5x', '20']));
    }

    public function testMaxFloat(): void
    {
        $this->assertSame(2.25, Items::maxFloat(['1.5', '2.25']));
    }

    public function testMinFloat(): void
    {
        $this->assertSame(1.5, Items::minFloat(['1.5', '2.25']));
    }

    public function testAdd(): void
    {
        $this->assertSame([11, 12, 13], Items::add([1, 2, 3], 10));
    }

    public function testSub(): void
    {
        $this->assertSame([0, 1, 2], Items::sub([1, 2, 3], 1));
    }

    public function testDiv(): void
    {
        $this->assertSame([1, 2], Items::div([10, 20], 10));
    }

    public function testMul(): void
    {
        $this->assertSame([2, 4, 6], Items::mul([1, 2, 3], 2));
    }

    public function testRename(): void
    {
        $array = ['old' => 5, 'z' => 9];

        Items::rename($array, 'old', 'new');

        $this->assertSame(['z' => 9, 'new' => 5], $array);
    }

    public function testReverse(): void
    {
        $this->assertSame([3, 2, 1], Items::reverse([1, 2, 3]));
    }

    public function testReverseKeys(): void
    {
        $this->assertSame(['c' => 3, 'b' => 2, 'a' => 1], Items::reverseKeys(['a' => 1, 'b' => 2, 'c' => 3]));
    }

    public function testFlip(): void
    {
        $this->assertSame(['a' => 0, 'b' => 1], Items::flip(['a', 'b']));
    }

    public function testSelect(): void
    {
        $this->assertSame([1, 2], Items::select([['id' => 1], ['id' => 2]], 'id'));
    }

    public function testSelectUnique(): void
    {
        $this->assertSame([1 => 1, 2 => 2], Items::selectUnique([['id' => 1], ['id' => 2], ['id' => 1]], 'id'));
    }

    public function testFields(): void
    {
        $this->assertSame(['a' => 1, 'c' => 3], Items::fields(['a' => 1, 'b' => 2, 'c' => 3], ['a', 'c']));
    }

    public function testUnique(): void
    {
        $this->assertSame([0 => 1, 2 => 2, 3 => 3], Items::unique([1, 1, 2, 3, 3]));
    }

    public function testAssoc(): void
    {
        $this->assertSame(['x' => 'x', 'y' => 'y'], Items::assoc(['x', 'y']));
        $this->assertSame([7 => ['id' => 7], 8 => ['id' => 8]], Items::assoc([['id' => 7], ['id' => 8]], 'id'));
    }

    public function testMap(): void
    {
        $this->assertSame(['a' => 1, 'b' => 2], Items::map([['k' => 'a', 'v' => 1], ['k' => 'b', 'v' => 2]], 'k', 'v'));
    }

    public function testWhere(): void
    {
        $items = [['n' => 1], ['n' => 2], ['n' => 1]];

        $this->assertSame([['n' => 1], ['n' => 1]], Items::where($items, 'n', 1));
    }

    public function testWhereNot(): void
    {
        $items = [['n' => 1], ['n' => 2]];

        $this->assertSame([['n' => 2]], Items::whereNot($items, 'n', 1));
    }

    public function testWhereGreater(): void
    {
        $this->assertSame([1 => ['n' => 5], 2 => ['n' => 3]], Items::whereGreater([['n' => 1], ['n' => 5], ['n' => 3]], 'n', 2));
    }

    public function testWhereGreaterOrEqual(): void
    {
        $this->assertSame([1 => ['n' => 2], 2 => ['n' => 3]], Items::whereGreaterOrEqual([['n' => 1], ['n' => 2], ['n' => 3]], 'n', 2));
    }

    public function testWhereLess(): void
    {
        $this->assertSame([0 => ['n' => 1]], Items::whereLess([['n' => 1], ['n' => 2], ['n' => 3]], 'n', 2));
    }

    public function testWhereLessOrEqual(): void
    {
        $this->assertSame([0 => ['n' => 1], 1 => ['n' => 2]], Items::whereLessOrEqual([['n' => 1], ['n' => 2], ['n' => 3]], 'n', 2));
    }

    public function testUnsetWhere(): void
    {
        $items = [['n' => 1], ['n' => 2], ['n' => 1]];

        Items::unsetWhere($items, 'n', 1);

        $this->assertSame([['n' => 2]], array_values($items));
    }

    public function testRepeat(): void
    {
        $this->assertSame(['x', 'x', 'x'], Items::repeat('x', 3));
    }

    public function testRemove(): void
    {
        $this->assertSame([0 => 1, 2 => 3], Items::remove([1, 2, 3, 4], [2, 4]));
    }

    public function testKeepKeysBulk(): void
    {
        $this->assertSame([['a' => 1], ['a' => 3]], Items::keepKeysBulk([['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], ['a']));
    }

    public function testRemoveKeysBulk(): void
    {
        $this->assertSame([['b' => 2], ['b' => 4]], Items::removeKeysBulk([['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], ['a']));
    }

    public function testRemoveEmpty(): void
    {
        $this->assertSame([1 => 1, 3 => 2, 5 => [3]], Items::removeEmpty([0, 1, '', 2, [], [3]]));
    }

    public function testRemoveStartsWith(): void
    {
        $this->assertSame([1 => 'banana'], Items::removeStartsWith(['apple', 'banana', 'apricot'], 'ap'));
    }

    public function testKeep(): void
    {
        $this->assertSame([1, 2, 3, 2], Items::keep([1, 2, 3, 2], 2));
    }

    public function testKeepEmails(): void
    {
        $this->assertSame([0 => 'a@b.com', 2 => 'c@d.com'], Items::keepEmails(['a@b.com', 'nope', 'c@d.com']));
    }

    public function testKeepStartWith(): void
    {
        $this->assertSame([0 => 'apple', 2 => 'apricot'], Items::keepStartWith(['apple', 'banana', 'apricot'], 'ap'));
    }

    public function testKeepKeysStartWith(): void
    {
        $this->assertSame(['apple' => 1, 'apricot' => 3], Items::keepKeysStartWith(['apple' => 1, 'banana' => 2, 'apricot' => 3], 'ap'));
    }

    public function testNestedExist(): void
    {
        $this->assertTrue(Items::nestedExist(['a' => ['b' => ['c' => 1]]], ['a', 'b', 'c']));

        $this->assertFalse(Items::nestedExist(['a' => ['b' => 1]], ['a', 'x']));
        $this->assertFalse(Items::nestedExist(['a' => 1], ['a', 'b'])); // descend past a scalar
    }

    public function testNestedGet(): void
    {
        $this->assertSame(5, Items::nestedGet(['a' => ['b' => 5]], ['a', 'b']));

        $this->assertSame('def', Items::nestedGet(['a' => 1], ['a', 'b'], 'def')); // descend past a scalar
    }

    public function testNestedGetPointer(): void
    {
        $array = ['a' => ['b' => 5]];

        $pointer = &Items::nestedGetPointer($array, ['a', 'b']);

        $this->assertSame(5, $pointer);
    }

    public function testNestedGetVerbose(): void
    {
        $this->assertSame([true, 5], Items::nestedGetVerbose(['a' => ['b' => 5]], ['a', 'b']));

        $this->assertSame([false, 'def'], Items::nestedGetVerbose(['a' => 1], ['x'], 'def'));
    }

    public function testNestedSet(): void
    {
        $array = ['a' => ['b' => 1]];

        Items::nestedSet($array, ['a', 'c'], 2);

        $this->assertSame(['a' => ['b' => 1, 'c' => 2]], $array);

        # a scalar sitting where a nested key is being walked/created gets overwritten with an array instead of crashing
        $array = ['a' => 5];

        Items::nestedSet($array, ['a', 'b'], 10);

        $this->assertSame(['a' => ['b' => 10]], $array);
    }

    public function testNestedPush(): void
    {
        $array = ['a' => []];

        Items::nestedPush($array, ['a'], 7);

        $this->assertSame(['a' => [7]], $array);

        # a scalar sitting where a nested key is being walked/created gets overwritten with an array instead of crashing
        $array = ['a' => 5];

        Items::nestedPush($array, ['a', 'b'], 10);

        $this->assertSame(['a' => ['b' => [10]]], $array);
    }

    public function testDiff(): void
    {
        $this->assertSame([0 => 1, 2 => 3], Items::diff([1, 2, 3], [2]));
    }

    public function testCommon(): void
    {
        $this->assertSame([1 => 2, 2 => 3], Items::common([1, 2, 3], [2, 3, 9]));
    }

    public function testImplode(): void
    {
        $this->assertSame('a-b-c', Items::implode(['a', 'b', 'c'], '-'));
    }

    public function testExplode(): void
    {
        $this->assertSame(['a', 'b', 'c'], Items::explode('a-b-c', '-'));
    }

    public function testChunk(): void
    {
        $this->assertSame([[1, 2], [3, 4], [5]], Items::chunk([1, 2, 3, 4, 5], 2, false));
    }

    public function testGroup(): void
    {
        $this->assertSame(
            ['x' => [['t' => 'x', 'n' => 1], ['t' => 'x', 'n' => 3]], 'y' => [['t' => 'y', 'n' => 2]]],
            Items::group([['t' => 'x', 'n' => 1], ['t' => 'y', 'n' => 2], ['t' => 'x', 'n' => 3]], 't')
        );
    }

    public function testFlat(): void
    {
        $this->assertSame([1, 2, 3, 4, 5], Items::flat([1, [2, [3, 4]], 5]));
    }

    public function testAppend(): void
    {
        $this->assertSame(['a!', 'b!'], Items::append(['a', 'b'], '!'));
        $this->assertSame(['a!', 'b!', 'c'], Items::append(['a', 'b', 'c'], '!', true, false, true));
    }

    public function testAppendBefore(): void
    {
        $this->assertSame(['!a', '!b'], Items::appendBefore(['a', 'b'], '!'));
    }

    public function testAppendAfter(): void
    {
        $this->assertSame(['a!', 'b!'], Items::appendAfter(['a', 'b'], '!'));
    }

    public function testAppendBoth(): void
    {
        $this->assertSame(['!a!', '!b!'], Items::appendBoth(['a', 'b'], '!'));
    }

    public function testPad(): void
    {
        $this->assertSame(['a  ', 'bbb', 'cc '], Items::pad(['a', 'bbb', 'cc']));
    }

    public function testPadLeft(): void
    {
        $this->assertSame(['  a', 'bbb'], Items::padLeft(['a', 'bbb']));
    }

    public function testPadRight(): void
    {
        $this->assertSame(['a  ', 'bbb'], Items::padRight(['a', 'bbb']));
    }

    public function testPadBoth(): void
    {
        $this->assertSame([' a  ', 'bbbb'], Items::padBoth(['a', 'bbbb']));
    }

    public function testNatksort(): void
    {
        $this->assertSame(['a1' => 3, 'a2' => 2, 'a10' => 1], Items::natksort(['a10' => 1, 'a2' => 2, 'a1' => 3]));
    }

    public function testIsAssoc(): void
    {
        $this->assertTrue(Items::isAssoc(['a' => 1]));

        $this->assertFalse(Items::isAssoc([1, 2, 3]));
    }
}

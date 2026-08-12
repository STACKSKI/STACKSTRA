<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Strings;

#[CoversClass(Strings::class)]
class StringsTest extends TestCase
{
    public function testRand(): void
    {
        $this->assertSame(5, Strings::length(Strings::rand(5)));
    }

    public function testRandMersenne(): void
    {
        $this->assertSame('', Strings::randMersenne(0));

        $this->assertSame('AAAAA', Strings::randMersenne(5, 'A'));
    }

    public function testFind(): void
    {
        $this->assertSame(13, Strings::find('ASCII string example', 'example'));

        $this->assertFalse(Strings::find('ASCII string example', 'xyz'));
    }

    public function testFindCI(): void
    {
        $this->assertSame(13, Strings::findCI('ASCII string example', 'EXAMPLE'));
    }

    public function testFindLast(): void
    {
        $this->assertSame(3, Strings::findLast('aXaXa', 'X'));
    }

    public function testFindLastCI(): void
    {
        $this->assertSame(3, Strings::findLastCI('aXaxa', 'X'));
    }

    public function testFindNth(): void
    {
        $this->assertSame(3, Strings::findNth('a.b.c.d', '.', 2));
    }

    public function testFindNthCI(): void
    {
        $this->assertSame(3, Strings::findNthCI('a.B.c', '.', 2));
    }

    public function testFindFirst(): void
    {
        $this->assertSame(1, Strings::findFirst('a.b.c', '.'));
    }

    public function testFindFirstCI(): void
    {
        $this->assertSame(1, Strings::findFirstCI('a.B.c', '.'));
    }

    public function testFindSecond(): void
    {
        $this->assertSame(3, Strings::findSecond('a.b.c.d', '.'));
    }

    public function testFindSecondCI(): void
    {
        $this->assertSame(3, Strings::findSecondCI('a.b.c.d', '.'));
    }

    public function testFindThird(): void
    {
        $this->assertSame(5, Strings::findThird('a.b.c.d', '.'));
    }

    public function testFindThirdCI(): void
    {
        $this->assertSame(5, Strings::findThirdCI('a.b.c.d', '.'));
    }

    public function testSize(): void
    {
        $this->assertSame(6, Strings::size('héllo'));
    }

    public function testLength(): void
    {
        $this->assertSame(5, Strings::length('héllo'));
    }

    public function testCount(): void
    {
        $this->assertSame(2, Strings::count('a.b.c', '.'));
    }

    public function testCountWords(): void
    {
        $this->assertSame(3, Strings::countWords('one two three'));
    }

    public function testCountLines(): void
    {
        $this->assertSame(3, Strings::countLines("a\nb\nc", "\n"));
    }

    public function testExcerpt(): void
    {
        $this->assertSame('Hello...', Strings::excerpt('Hello world', 8));
        $this->assertSame('Hi', Strings::excerpt('Hi', 8));
    }

    public function testReplace(): void
    {
        $this->assertSame('ASCII string sample', Strings::replace('ASCII string example', 'example', 'sample'));
        $this->assertSame('X string X', Strings::replace('ASCII string example', ['ASCII', 'example'], 'X'));
    }

    public function testReplaceFirst(): void
    {
        $this->assertSame('X.b.a', Strings::replaceFirst('a.b.a', 'a', 'X'));
    }

    public function testReplaceLast(): void
    {
        $this->assertSame('a.b.X', Strings::replaceLast('a.b.a', 'a', 'X'));
    }

    public function testReplaceBetween(): void
    {
        $this->assertSame('<a>X<b>', Strings::replaceBetween('<a>keep<b>', '<a>', '<b>', 'X'));
    }

    public function testRemove(): void
    {
        $this->assertSame('.b.', Strings::remove('a.b.a', 'a'));
    }

    public function testRemoveFirst(): void
    {
        $this->assertSame('.b.a', Strings::removeFirst('a.b.a', 'a'));
    }

    public function testRemoveLast(): void
    {
        $this->assertSame('a.b.', Strings::removeLast('a.b.a', 'a'));
    }

    public function testRemoveEmptyLines(): void
    {
        $this->assertSame("a\nb\nc", Strings::removeEmptyLines("a\n\nb\n   \nc"));
    }

    public function testRemoveBetween(): void
    {
        $this->assertSame('x<a><b>y', Strings::removeBetween('x<a>drop<b>y', '<a>', '<b>'));
    }

    public function testRead(): void
    {
        $this->assertSame('examp', Strings::read('ASCII string example', 5, 13));
    }

    public function testReadAfter(): void
    {
        $this->assertSame('example', Strings::readAfter('ASCII string example', 'string '));

        $this->assertNull(Strings::readAfter('ASCII string example', 'zzz'));
    }

    public function testReadFrom(): void
    {
        $this->assertSame('string example', Strings::readFrom('ASCII string example', 'string'));
    }

    public function testReadUntil(): void
    {
        $this->assertSame('ASCII ', Strings::readUntil('ASCII string example', 'string'));
        $this->assertSame('ASCII string', Strings::readUntil('ASCII string example', 'string', false));
    }

    public function testReadBetween(): void
    {
        $this->assertSame('amp', Strings::readBetween('ASCII string example', 'ex', 'le'));
        $this->assertSame('examp', Strings::readBetween('ASCII string example', 'ex', 'le', true));
    }

    public function testReverse(): void
    {
        $this->assertSame('cba', Strings::reverse('abc'));
    }

    public function testTrim(): void
    {
        $this->assertSame('hi', Strings::trim('  hi  '));
    }

    public function testTrimLeft(): void
    {
        $this->assertSame('hi  ', Strings::trimLeft('  hi  '));
    }

    public function testTrimRight(): void
    {
        $this->assertSame('  hi', Strings::trimRight('  hi  '));
    }

    public function testExplode(): void
    {
        $this->assertSame(['a', 'b', 'c'], Strings::explode('a,b,c', ','));
    }

    public function testImplode(): void
    {
        $this->assertSame('a-b-c', Strings::implode(['a', 'b', 'c'], '-'));
    }

    public function testContains(): void
    {
        $this->assertTrue(Strings::contains('ASCII string example', 'string'));

        $this->assertFalse(Strings::contains('ASCII string example', 'zzz'));
    }

    public function testContainsAny(): void
    {
        $this->assertTrue(Strings::containsAny('ASCII string example', ['zzz', 'string']));

        $this->assertFalse(Strings::containsAny('ASCII string example', ['zzz', 'yyy']));
    }

    public function testContainsAll(): void
    {
        $this->assertTrue(Strings::containsAll('ASCII string example', ['ASCII', 'example']));

        $this->assertFalse(Strings::containsAll('ASCII string example', ['ASCII', 'zzz']));
    }

    public function testContainsOnly(): void
    {
        $this->assertTrue(Strings::containsOnly('aba', ['a', 'b']));

        $this->assertFalse(Strings::containsOnly('abc', ['a', 'b']));
    }

    public function testContainsLetters(): void
    {
        $this->assertTrue(Strings::containsLetters('abc123'));

        $this->assertFalse(Strings::containsLetters('123'));
    }

    public function testContainsDigits(): void
    {
        $this->assertTrue(Strings::containsDigits('abc123'));

        $this->assertFalse(Strings::containsDigits('abc'));
    }

    public function testStartsWith(): void
    {
        $this->assertTrue(Strings::startsWith('ASCII string example', 'ASCII'));
        $this->assertTrue(Strings::startsWith('ASCII string example', 'ascii', false));

        $this->assertFalse(Strings::startsWith('ASCII string example', 'example'));
    }

    public function testEndsWith(): void
    {
        $this->assertTrue(Strings::endsWith('ASCII string example', 'example'));
        $this->assertTrue(Strings::endsWith('ASCII string example', 'EXAMPLE', false));

        $this->assertFalse(Strings::endsWith('ASCII string example', 'ASCII'));
    }

    public function testErase(): void
    {
        $this->assertSame('he', Strings::erase('hello', 2));
        $this->assertSame('llo', Strings::erase('hello', 0, 2));
        $this->assertSame('heo', Strings::erase('hello', 2, 2));
    }

    public function testInsert(): void
    {
        $this->assertSame('AAAAXBBBB', Strings::insert('AAAABBBB', 'X', 4));
    }

    public function testPermutation(): void
    {
        $this->assertSame(['A B', 'B A'], Strings::permutation('A B'));
    }

    public function testFirst(): void
    {
        $this->assertSame('Hello', Strings::first("Hello\nHi\nWow!\n12345", "\n"));
    }

    public function testLast(): void
    {
        $this->assertSame('12345', Strings::last("Hello\nHi\nWow!\n12345", "\n"));
    }

    public function testChar(): void
    {
        $this->assertSame('e', Strings::char('ASCII string example', 14));
    }

    public function testChars(): void
    {
        $this->assertSame(['a', 'b', 'c'], Strings::chars('abc'));
    }

    public function testBytes(): void
    {
        $this->assertSame([1 => 65, 2 => 66], Strings::bytes('AB'));
    }

    public function testSplit(): void
    {
        $this->assertSame(['ab', 'cd', 'ef'], Strings::split('abcdef', 2));
    }

    public function testLine(): void
    {
        $this->assertSame('Wow!', Strings::line("Hello\nHi\nWow!\n12345", 3, "\n"));

        $this->assertNull(Strings::line("Hello\nHi\nWow!\n12345", 99, "\n"));
    }

    public function testLines(): void
    {
        $this->assertSame(['b', 'c'], Strings::lines("a\nb\nc", 2, 3, "\n"));
    }

    public function testLinesAppend(): void
    {
        $this->assertSame("a!\nb!", Strings::linesAppend("a\nb", '!', null, null, "\n"));
    }

    public function testLines_append_before(): void
    {
        $this->assertSame("!a\n!b", Strings::lines_append_before("a\nb", '!', null, null, "\n"));
    }

    public function testLines_append_after(): void
    {
        $this->assertSame("a!\nb!", Strings::lines_append_after("a\nb", '!', null, null, "\n"));
    }

    public function testLines_append_both(): void
    {
        $this->assertSame("!a!\n!b!", Strings::lines_append_both("a\nb", '!', null, null, "\n"));
    }

    public function testWord(): void
    {
        $this->assertSame('string', Strings::word('ASCII string example', 2));
    }

    public function testWords(): void
    {
        $this->assertSame(['ASCII', 'string', 'example'], Strings::words('ASCII string example'));
    }

    public function testDigits(): void
    {
        $this->assertSame('123', Strings::digits('+1a2b3'));
    }

    public function testNumber(): void
    {
        $this->assertSame('-123', Strings::number('-1a2b3'));
    }

    public function testEscape(): void
    {
        $this->assertSame('ab', Strings::escape('a1!b2@', 'ab'));
    }

    public function testEqual(): void
    {
        $this->assertTrue(Strings::equal('abc', 'abc'));
        $this->assertTrue(Strings::equal('abc', 'ABC', false));

        $this->assertFalse(Strings::equal('abc', 'xyz'));
    }

    public function testEqualCI(): void
    {
        $this->assertTrue(Strings::equalCI('abc', 'ABC'));

        $this->assertFalse(Strings::equalCI('abc', 'xyz'));
    }

    public function testIs_utf8(): void
    {
        $this->assertTrue(Strings::is_utf8('abc'));
    }

    public function testIsBom(): void
    {
        $this->assertTrue(Strings::isBom("\xef\xbb\xbfabc"));

        $this->assertFalse(Strings::isBom('abc'));
    }

    public function testIsSerialized(): void
    {
        $this->assertTrue(Strings::isSerialized('i:42;'));

        $this->assertFalse(Strings::isSerialized('nope'));
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(Strings::isEmpty(''));

        $this->assertFalse(Strings::isEmpty('x'));
    }

    public function testIsEmail(): void
    {
        $this->assertTrue(Strings::isEmail('a@b.com'));

        $this->assertFalse(Strings::isEmail('nope'));
    }

    public function testIsIP(): void
    {
        $this->assertTrue(Strings::isIP('127.0.0.1'));

        $this->assertFalse(Strings::isIP('nope'));
    }

    public function testIsUppercase(): void
    {
        $this->assertTrue(Strings::isUppercase('ABC'));

        $this->assertFalse(Strings::isUppercase('Abc'));
    }

    public function testIsLowercase(): void
    {
        $this->assertTrue(Strings::isLowercase('abc'));

        $this->assertFalse(Strings::isLowercase('Abc'));
    }

    public function testIsURL(): void
    {
        $this->assertTrue(Strings::isURL('http://x.com'));

        $this->assertFalse(Strings::isURL('nope'));
    }

    public function testIs_number(): void
    {
        $this->assertTrue(Strings::is_number('42'));

        $this->assertFalse(Strings::is_number('x'));
    }

    public function testIsInteger(): void
    {
        $this->assertTrue(Strings::isInteger('42'));

        $this->assertFalse(Strings::isInteger('4.2'));
    }

    public function testIsJSON(): void
    {
        $this->assertTrue(Strings::isJSON('{"a":1}'));

        $this->assertFalse(Strings::isJSON('{bad'));
    }

    public function testParseFloat(): void
    {
        $this->assertSame(4.2, Strings::parseFloat('4.2abc'));
    }

    public function testParseInt(): void
    {
        $this->assertSame(42, Strings::parseInt('42abc'));
    }

    public function testUnpack(): void
    {
        $this->assertSame(65, Strings::unpack('C', 'A'));
    }

    public function testPad(): void
    {
        $this->assertSame('x---', Strings::pad('x', 4, '-'));
    }

    public function testPadLeft(): void
    {
        $this->assertSame('---x', Strings::padLeft('x', 4, '-'));
    }

    public function testPadRight(): void
    {
        $this->assertSame('x---', Strings::padRight('x', 4, '-'));
    }

    public function testPadBoth(): void
    {
        $this->assertSame('--x--', Strings::padBoth('x', 5, '-'));
    }

    public function testRepeat(): void
    {
        $this->assertSame('ababab', Strings::repeat('ab', 3));
    }

    public function testToUTF8(): void
    {
        $this->assertSame('abc', Strings::toUTF8('abc'));
    }

    public function testToUppercase(): void
    {
        $this->assertSame('ABC', Strings::toUppercase('abc'));
    }

    public function testToLowercase(): void
    {
        $this->assertSame('abc', Strings::toLowercase('ABC'));
    }

    public function testToCapitalcase(): void
    {
        $this->assertSame('Hello', Strings::toCapitalcase('heLLO'));
    }

    public function testToTitlecase(): void
    {
        $this->assertSame('Hello World', Strings::toTitlecase('hello world'));
    }

    public function testToSnakecase(): void
    {
        $this->assertSame('hello_world', Strings::toSnakecase('helloWorld'));
    }

    public function testRemoveDuplicates(): void
    {
        $this->assertSame('a b', Strings::removeDuplicates('a a b a', ' '));
    }

    public function testRemoveNumbers(): void
    {
        $this->assertSame('abc', Strings::removeNumbers('a1b2c3'));
    }

    public function testRemoveBOM(): void
    {
        $this->assertSame('abc', Strings::removeBOM("\xef\xbb\xbfabc"));
    }
}

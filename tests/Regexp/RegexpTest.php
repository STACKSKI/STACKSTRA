<?php

namespace Stackstra\Tests\Regexp;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Regexp\Regexp;
use Stackstra\Tests\TestCase;

#[CoversClass(Regexp::class)]
class RegexpTest extends TestCase
{
    public function testKeep(): void
    {
        // null input passes through untouched, replace argument or not
        $this->assertNull(Regexp::keep(null, Regexp::ALPHA));
        $this->assertNull(Regexp::keep(null, Regexp::ALPHA, '-'));

        // default replace (null) strips non-matching characters entirely
        $this->assertSame('abc', Regexp::keep('a1b2c3', Regexp::ALPHA));

        // explicit replace substitutes non-matching characters instead of removing them
        $this->assertSame('a-b-c-', Regexp::keep('a1b2c3', Regexp::ALPHA, '-'));

        // case-insensitive: pattern 'a-z' also keeps uppercase letters
        $this->assertSame('abcABC', Regexp::keep('abc123ABC', Regexp::ALPHA));
    }

    public function testKeepNum(): void
    {
        $this->assertNull(Regexp::keepNum(null));
        $this->assertSame('123', Regexp::keepNum('a1b2c3'));
        $this->assertSame('x1x2x3', Regexp::keepNum('a1b2c3', 'x'));
    }

    public function testKeepAlpha(): void
    {
        $this->assertNull(Regexp::keepAlpha(null));
        $this->assertSame('abcABC', Regexp::keepAlpha('a1b2c3ABC'));
        $this->assertSame('a-b-c-ABC', Regexp::keepAlpha('a1b2c3ABC', '-'));
    }

    public function testKeepAlphaNum(): void
    {
        $this->assertNull(Regexp::keepAlphaNum(null));
        $this->assertSame('abc123', Regexp::keepAlphaNum('abc123!@#'));
        $this->assertSame('abc123---', Regexp::keepAlphaNum('abc123!@#', '-'));
    }

    public function testKeepAlphaNumBrackets(): void
    {
        $this->assertNull(Regexp::keepAlphaNumBrackets(null));
        $this->assertSame('abc123[]', Regexp::keepAlphaNumBrackets('abc123[]!@#'));
        $this->assertSame('abc123[]---', Regexp::keepAlphaNumBrackets('abc123[]!@#', '-'));
    }

    public function testKeepAlphaNumUnderscore(): void
    {
        $this->assertNull(Regexp::keepAlphaNumUnderscore(null));
        $this->assertSame('abc_123', Regexp::keepAlphaNumUnderscore('abc_123!@#'));
        $this->assertSame('abc_123---', Regexp::keepAlphaNumUnderscore('abc_123!@#', '-'));
    }

    public function testKeepAlphaNumUnderscoreBrackets(): void
    {
        $this->assertNull(Regexp::keepAlphaNumUnderscoreBrackets(null));
        $this->assertSame('abc_123[]', Regexp::keepAlphaNumUnderscoreBrackets('abc_123[]!@#'));
        $this->assertSame('abc_123[]---', Regexp::keepAlphaNumUnderscoreBrackets('abc_123[]!@#', '-'));
    }

    public function testKeepAlphaNumUnderscoreAt(): void
    {
        $this->assertNull(Regexp::keepAlphaNumUnderscoreAt(null));
        $this->assertSame('abc_123@x', Regexp::keepAlphaNumUnderscoreAt('abc_123@x!#'));
        $this->assertSame('abc_123@x--', Regexp::keepAlphaNumUnderscoreAt('abc_123@x!#', '-'));
    }

    public function testKeepAlphaNumUnderscoreDash(): void
    {
        $this->assertNull(Regexp::keepAlphaNumUnderscoreDash(null));
        $this->assertSame('abc_123-x', Regexp::keepAlphaNumUnderscoreDash('abc_123-x!#'));
        $this->assertSame('abc_123-x__', Regexp::keepAlphaNumUnderscoreDash('abc_123-x!#', '_'));
    }

    public function testKeepAlphaNumUnderscoreDashBackslash(): void
    {
        $this->assertNull(Regexp::keepAlphaNumUnderscoreDashBackslash(null));
        $this->assertSame('abc_123-x\\', Regexp::keepAlphaNumUnderscoreDashBackslash('abc_123-x\\!#'));
        $this->assertSame('abc_123-x\\__', Regexp::keepAlphaNumUnderscoreDashBackslash('abc_123-x\\!#', '_'));
    }

    public function testKeepAlphaNumUnderscoreDot(): void
    {
        $this->assertNull(Regexp::keepAlphaNumUnderscoreDot(null));
        $this->assertSame('abc_123.x', Regexp::keepAlphaNumUnderscoreDot('abc_123.x!#'));
        $this->assertSame('abc_123.x__', Regexp::keepAlphaNumUnderscoreDot('abc_123.x!#', '_'));
    }

    public function testKeepAlphaNumUnderscoreDotDash(): void
    {
        $this->assertNull(Regexp::keepAlphaNumUnderscoreDotDash(null));
        $this->assertSame('abc_123.x-y', Regexp::keepAlphaNumUnderscoreDotDash('abc_123.x-y!#'));
        $this->assertSame('abc_123.x-y__', Regexp::keepAlphaNumUnderscoreDotDash('abc_123.x-y!#', '_'));
    }

    public function testParseNumbers(): void
    {
        // null input yields an empty array
        $this->assertSame([], Regexp::parseNumbers(null));

        // no digits present also yields an empty array
        $this->assertSame([], Regexp::parseNumbers('no digits here'));

        // consecutive digit runs are extracted as separate strings, in order
        $this->assertSame(['12', '345', '6'], Regexp::parseNumbers('ab12cd345ef6'));
    }
}

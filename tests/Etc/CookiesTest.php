<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\Cookies;
use Stackstra\Tests\TestCase;

#[CoversClass(Cookies::class)]
class CookiesTest extends TestCase
{
    protected function tearDown(): void
    {
        $_COOKIE = [];
    }

    public function testGet(): void
    {
        $_COOKIE = ['a' => '1'];

        // no name: the whole $_COOKIE array
        $this->assertSame(['a' => '1'], Cookies::get());

        // name present
        $this->assertSame('1', Cookies::get('a'));

        // name missing: null
        $this->assertNull(Cookies::get('z'));
    }

    public function testInc(): void
    {
        $_COOKIE = ['a' => 5];

        // default increment of 1
        $this->silently(fn () => Cookies::inc('a'));
        $this->assertSame(6, $_COOKIE['a']);

        // explicit increment
        $this->silently(fn () => Cookies::inc('a', 4));
        $this->assertSame(10, $_COOKIE['a']);
    }

    public function testDec(): void
    {
        $_COOKIE = ['a' => 5];

        // default decrement of 1
        $this->silently(fn () => Cookies::dec('a'));
        $this->assertSame(4, $_COOKIE['a']);

        // explicit decrement
        $this->silently(fn () => Cookies::dec('a', 4));
        $this->assertSame(0, $_COOKIE['a']);
    }

    public function testSet(): void
    {
        // setcookie() fails under CLI (headers can't be sent), but $_COOKIE is still updated
        $this->silently(fn () => Cookies::set('a', 'value'));

        $this->assertSame('value', $_COOKIE['a']);
    }

    public function testDelete(): void
    {
        $_COOKIE = ['a' => '1', 'b' => '2'];

        // explicit name
        $this->silently(fn () => Cookies::delete('a'));
        $this->assertArrayNotHasKey('a', $_COOKIE);

        // name omitted: deletes every cookie
        $this->assertTrue($this->silently(fn () => Cookies::delete()));
        $this->assertSame([], $_COOKIE);
    }

    public function testIsExist(): void
    {
        $_COOKIE = ['a' => '1'];

        // no value argument: existence check only
        $this->assertTrue(Cookies::isExist('a'));
        $this->assertFalse(Cookies::isExist('z'));

        // value argument: also compares the value (loose)
        $this->assertTrue(Cookies::isExist('a', '1'));
        $this->assertTrue(Cookies::isExist('a', 1));
        $this->assertFalse(Cookies::isExist('a', '2'));
    }
}

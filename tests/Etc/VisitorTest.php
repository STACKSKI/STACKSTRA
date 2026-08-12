<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\Visitor;
use Stackstra\Tests\TestCase;

#[CoversClass(Visitor::class)]
class VisitorTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_REFERER', 'HTTP_USER_AGENT'] as $key)
        {
            unset($_SERVER[$key]);
        }
    }

    public function testIP(): void
    {
        // CloudFlare header takes priority and overrides the other two fields
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '1.2.3.4';
        $_SERVER['HTTP_CLIENT_IP']        = '9.9.9.9';
        $_SERVER['REMOTE_ADDR']           = '9.9.9.9';

        $this->assertSame('1.2.3.4', Visitor::ip());
        $this->assertSame($_SERVER['HTTP_CLIENT_IP'], '1.2.3.4');

        // fallback chain: HTTP_CLIENT_IP > HTTP_X_FORWARDED_FOR > REMOTE_ADDR
        unset($_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['HTTP_CLIENT_IP']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '2.2.2.2';
        $_SERVER['REMOTE_ADDR']          = '3.3.3.3';
        $this->assertSame('2.2.2.2', Visitor::ip());

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $this->assertSame('3.3.3.3', Visitor::ip());

        // invalid IP: null
        $_SERVER['REMOTE_ADDR'] = 'not-an-ip';
        $this->assertNull(Visitor::ip());

        // to_integer=true
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->assertSame(ip2long('127.0.0.1'), Visitor::ip(true));
    }

    public function testLanguage(): void
    {
        // no Accept-Language header: empty string
        $this->assertSame('', Visitor::language());

        // first entry's country code, lowercased, stripped of any region suffix
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US;q=0.9,fr;q=0.8';
        $this->assertSame('en', Visitor::language());
    }

    public function testLanguages(): void
    {
        $this->assertSame([], Visitor::languages());

        // duplicates (after formatting) are removed
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US;q=0.9,EN-GB;q=0.8,fr;q=0.7';
        $this->assertSame(['en' => 'en', 'fr' => 'fr'], Visitor::languages());
    }

    public function testIsLanguage(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US;q=0.9';

        $this->assertTrue(Visitor::isLanguage('en'));
        $this->assertTrue(Visitor::isLanguage('EN')); // format() lowercases both sides
        $this->assertFalse(Visitor::isLanguage('fr'));
    }

    public function testIsEnglish(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US;q=0.9';

        $this->assertTrue(Visitor::isEnglish());
        $this->assertFalse(Visitor::isSpanish());
    }

    public function testIsSpanish(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es;q=0.9';

        $this->assertTrue(Visitor::isSpanish());
    }

    public function testIsChinese(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'zh;q=0.9';

        $this->assertTrue(Visitor::isChinese());
    }

    public function testIsRussian(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru;q=0.9';

        $this->assertTrue(Visitor::isRussian());
    }

    public function testReferer(): void
    {
        $this->assertNull(Visitor::referer());

        $_SERVER['HTTP_REFERER'] = 'https://from.example';
        $this->assertSame('https://from.example', Visitor::referer());
    }

    public function testIsReferer(): void
    {
        $_SERVER['HTTP_REFERER'] = 'https://EXAMPLE.com/path';

        // case-insensitive by default
        $this->assertTrue(Visitor::isReferer('example.com'));

        // case-sensitive=true
        $this->assertFalse(Visitor::isReferer('example.com', true));
        $this->assertTrue(Visitor::isReferer('EXAMPLE.com', true));
    }

    public function testUserAgent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'MyBrowser/1.0';

        $this->assertSame('MyBrowser/1.0', Visitor::userAgent());
    }

    public function testIsAndroid(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Android 10';
        $this->assertTrue(Visitor::isAndroid());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla iPhone';
        $this->assertFalse(Visitor::isAndroid());
    }

    public function testIsWindowsPhone(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Windows Phone 10';
        $this->assertTrue(Visitor::isWindowsPhone());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Android';
        $this->assertFalse(Visitor::isWindowsPhone());
    }

    public function testIsIphone(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla iPhone OS';
        $this->assertTrue(Visitor::isIphone());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Android';
        $this->assertFalse(Visitor::isIphone());
    }

    public function testIsIpad(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla iPad';
        $this->assertTrue(Visitor::isIpad());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Android';
        $this->assertFalse(Visitor::isIpad());
    }

    public function testIsIpod(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla iPod';
        $this->assertTrue(Visitor::isIpod());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Android';
        $this->assertFalse(Visitor::isIpod());
    }

    public function testIsPhone(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Android';
        $this->assertTrue(Visitor::isPhone());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla iPad';
        $this->assertTrue(Visitor::isPhone());

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla Desktop Browser';
        $this->assertFalse(Visitor::isPhone());
    }
}

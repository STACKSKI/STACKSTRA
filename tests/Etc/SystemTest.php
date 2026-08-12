<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\System;

#[CoversClass(System::class)]
class SystemTest extends TestCase
{
    public function testIsLittleEndian(): void
    {
        $this->assertSame(!System::isBigEndian(), System::isLittleEndian());
    }

    public function testIsBigEndian(): void
    {
        $this->assertSame(!System::isLittleEndian(), System::isBigEndian());
    }

    public function testUname(): void
    {
        $this->assertSame(php_uname('s'), System::uname('s'));
    }

    public function testHostname(): void
    {
        $this->assertSame(php_uname('n'), System::hostname());
    }

    public function testProcessor(): void
    {
        $this->assertSame(php_uname('m'), System::processor());
    }

    public function testOs(): void
    {
        $this->assertSame(php_uname('s'), System::os());
    }

    public function testIsOsLinux(): void
    {
        $this->assertSame(str_starts_with(strtolower(PHP_OS), 'linux'), System::isOsLinux());
    }

    public function testIsOsWindows(): void
    {
        $this->assertSame(str_starts_with(strtolower(PHP_OS), 'win'), System::isOsWindows());
    }

    public function testIsOsMac(): void
    {
        $this->assertSame(str_starts_with(strtolower(PHP_OS), 'darwin'), System::isOsMac());
    }

    public function testIsOsFreebsd(): void
    {
        $this->assertSame(str_starts_with(strtolower(PHP_OS), 'freebsd'), System::isOsFreebsd());
    }
}

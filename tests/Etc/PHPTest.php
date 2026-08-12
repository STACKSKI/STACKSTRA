<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\PHP;
use Stackstra\Tests\TestCase;

#[CoversClass(PHP::class)]
class PHPTest extends TestCase
{
    public function testGid(): void
    {
        $this->assertSame(getmygid(), PHP::gid());
    }

    public function testPid(): void
    {
        $this->assertSame(getmypid(), PHP::pid());
    }

    public function testUid(): void
    {
        $this->assertSame(getmyuid(), PHP::uid());
    }

    public function testMemoryUsage(): void
    {
        // default: real_usage=true
        $this->assertGreaterThan(0, PHP::memoryUsage());

        // explicit false
        $this->assertGreaterThan(0, PHP::memoryUsage(false));
    }

    public function testMemoryUsagePeak(): void
    {
        $this->assertGreaterThan(0, PHP::memoryUsagePeak());
        $this->assertGreaterThan(0, PHP::memoryUsagePeak(false));
    }

    public function testMemoryUsageMegabytes(): void
    {
        $this->assertGreaterThan(0, PHP::memoryUsageMegabytes());
        $this->assertGreaterThan(0, PHP::memoryUsageMegabytes(false));
    }

    public function testMemoryUsagePeakMegabytes(): void
    {
        $this->assertGreaterThan(0, PHP::memoryUsagePeakMegabytes());
        $this->assertGreaterThan(0, PHP::memoryUsagePeakMegabytes(false));
    }

    public function testMemoryUsageReport(): void
    {
        // writes lines to the console; nothing to assert on the return value, just that it runs
        ob_start();
        PHP::memoryUsageReport();
        $output = ob_get_clean();

        $this->assertStringContainsString('[memory]:', $output);
        $this->assertStringContainsString('allocated:', $output);
        $this->assertStringContainsString('used:', $output);
    }

    public function testUser(): void
    {
        $this->assertSame(get_current_user(), PHP::user());
    }

    public function testVersion(): void
    {
        $this->assertSame(phpversion(), PHP::version());
    }

    public function testVersionCompare(): void
    {
        $this->assertTrue(PHP::versionCompare('2.0', '1.0', '>'));
        $this->assertFalse(PHP::versionCompare('1.0', '2.0', '>'));
        $this->assertTrue(PHP::versionCompare('1.0', '1.0', '=='));
    }

    public function testIs32(): void
    {
        $this->assertSame(PHP_INT_SIZE === 4, PHP::is32());
    }

    public function testIs64(): void
    {
        $this->assertSame(PHP_INT_SIZE === 8, PHP::is64());
    }

    public function testMinInt(): void
    {
        $this->assertSame(PHP_INT_MIN, PHP::minInt());
    }

    public function testMaxInt(): void
    {
        $this->assertSame(PHP_INT_MAX, PHP::maxInt());
    }
}

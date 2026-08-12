<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\Hardware;
use Stackstra\Tests\TestCase;

#[CoversClass(Hardware::class)]
class HardwareTest extends TestCase
{
    public function testCores(): void
    {
        $cores = Hardware::cores();

        // whatever detection path ran (Linux /proc/cpuinfo here), result is clamped to at least 1
        $this->assertIsInt($cores);
        $this->assertGreaterThanOrEqual(1, $cores);

        // repeated calls return the same cached value
        $this->assertSame($cores, Hardware::cores());
    }
}

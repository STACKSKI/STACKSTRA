<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\Process;
use Stackstra\Tests\TestCase;

#[CoversClass(Process::class)]
class ProcessTest extends TestCase
{
    public function testLeader(): void
    {
        // posix_setsid() fails (-1) when the calling process is already a session leader,
        // which the PHPUnit test runner's process typically is
        $this->assertSame(-1, Process::leader());
    }

    public function testIsExist(): void
    {
        // signal 0 to our own PID: always alive
        $this->assertTrue(Process::isExist(getmypid()));

        // an unlikely-to-exist PID
        $this->assertFalse(@Process::isExist(999999));
    }
}

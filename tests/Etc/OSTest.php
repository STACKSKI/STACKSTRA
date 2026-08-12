<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\OS;
use Stackstra\Tests\TestCase;

#[CoversClass(OS::class)]
class OSTest extends TestCase
{
    public function testConstants(): void
    {
        // exactly one of the known-family constants matches the running OS, or none do
        $known = [OS::IS_WINDOWS, OS::IS_BSD, OS::IS_DARWIN, OS::IS_SOLARIS, OS::IS_LINUX];

        $this->assertSame(array_sum($known) <= 1, true);

        // IS_UNKNOWN is true exactly when none of the known families matched
        $this->assertSame(array_sum($known) === 0, OS::IS_UNKNOWN);

        // on this Linux CI/dev environment specifically:
        $this->assertTrue(OS::IS_LINUX);
        $this->assertFalse(OS::IS_WINDOWS);
        $this->assertFalse(OS::IS_UNKNOWN);
    }
}

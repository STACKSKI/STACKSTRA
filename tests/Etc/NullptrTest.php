<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\Nullptr;

#[CoversClass(Nullptr::class)]
class NullptrTest extends TestCase
{
    public function testInstance(): void
    {
        $this->assertInstanceOf(Nullptr::class, Nullptr::instance());

        $this->assertSame(Nullptr::instance(), Nullptr::instance()); // same shared singleton
    }
}

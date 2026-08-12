<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\Buffering;
use Stackstra\Tests\TestCase;

#[CoversClass(Buffering::class)]
class BufferingTest extends TestCase
{
    public function testStart(): void
    {
        $level = ob_get_level();

        $this->assertTrue(Buffering::start());

        $this->assertSame($level + 1, ob_get_level());

        ob_end_clean();
    }

    public function testLevel(): void
    {
        $before = Buffering::level();

        ob_start();

        $this->assertSame($before + 1, Buffering::level());

        ob_end_clean();
    }

    public function testGetClean(): void
    {
        ob_start();
        echo 'hello';

        $this->assertSame('hello', Buffering::getClean());
    }

    public function testEndClean(): void
    {
        $level = ob_get_level();

        ob_start();
        echo 'discarded';

        $this->assertTrue(Buffering::endClean());

        $this->assertSame($level, ob_get_level());
    }
}

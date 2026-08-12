<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\HTML;

#[CoversClass(HTML::class)]
class HTMLTest extends TestCase
{
    public function testEscape(): void
    {
        $this->assertSame('&lt;b&gt;&quot;&amp;', HTML::escape('<b>"&'));

        $this->assertSame('a&amp;b', HTML::escape('a&b'));
    }
}

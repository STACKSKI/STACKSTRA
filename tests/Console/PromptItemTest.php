<?php

namespace Stackstra\Tests\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Console\PromptItem;
use Stackstra\Tests\TestCase;

#[CoversClass(PromptItem::class)]
class PromptItemTest extends TestCase
{
    public function testConstruct(): void
    {
        // the constructor body is entirely commented out: it must simply not throw
        $item = new PromptItem('label', 'value', fn () => null);

        $this->assertInstanceOf(PromptItem::class, $item);
    }

    public function testInstance(): void
    {
        // the method body is entirely commented out: no return value
        $result = PromptItem::instance('label', 'value', fn () => null);

        $this->assertNull($result);
    }
}

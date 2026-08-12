<?php

namespace Stackstra\Tests\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Stackstra\Console\Prompt;
use Stackstra\Tests\TestCase;

#[CoversClass(Prompt::class)]
class PromptTest extends TestCase
{
    protected function tearDown(): void
    {
        // Prompt::$arguments is shared static state, reset it between tests
        Prompt::arguments([]);
    }

    public function testConstruct(): void
    {
        $called = false;

        $prompt = new Prompt([['a', 'A', fn () => $called = true, []]]);

        $this->assertSame('q', $prompt->optionQuit());
        $this->assertFalse($called);

        // explicit option_quit
        $prompt = new Prompt([], 'x');
        $this->assertSame('x', $prompt->optionQuit());

        // option_quit=null disables the quit option
        $prompt = new Prompt([], null);
        $this->assertNull($prompt->optionQuit());
    }

    public function testMake(): void
    {
        $prompt = Prompt::make();

        $this->assertInstanceOf(Prompt::class, $prompt);
        $this->assertSame('q', $prompt->optionQuit());
    }

    public function testArguments(): void
    {
        Prompt::arguments(['a']);

        $ref  = new ReflectionClass(Prompt::class);
        $prop = $ref->getProperty('arguments');

        $this->assertSame(['a'], $prop->getValue());
    }

    public function testAdd(): void
    {
        $prompt = Prompt::make();

        $result = $prompt->add('a', 'Option A', fn () => null);

        $this->assertSame($prompt, $result);
    }

    public function testAddBulk(): void
    {
        $prompt = Prompt::make();

        $result = $prompt->addBulk([
            ['a', 'Option A', fn () => null],
            ['b', 'Option B', fn () => null, ['x']],
        ]);

        $this->assertSame($prompt, $result);
    }

    public function testOptionQuit(): void
    {
        $prompt = Prompt::make();

        // getter (no argument)
        $this->assertSame('q', $prompt->optionQuit());

        // setter
        $result = $prompt->optionQuit('x');
        $this->assertSame($prompt, $result);
        $this->assertSame('x', $prompt->optionQuit());
    }

    public function testRun(): void
    {
        $called_with = null;

        $prompt = new Prompt([['a', 'Option A', function (...$args) use (&$called_with) { $called_with = $args; }, ['x', 'y']]]);

        // driving the loop entirely via CLI-supplied arguments avoids any blocking readline() call
        Prompt::arguments(['a']);

        ob_start();
        $prompt->run();
        ob_get_clean();

        $this->assertSame(['x', 'y'], $called_with);

        // an unrecognized option is reported, then the loop consumes the next queued argument
        Prompt::arguments(['bogus', 'q']);

        ob_start();
        $prompt->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('bogus does not exist', $output);
    }
}

<?php

namespace Stackstra\Tests\Exceptions;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Exceptions\Exceptions;
use Stackstra\Tests\TestCase;

#[CoversClass(Exceptions::class)]
class ExceptionsTest extends TestCase
{
    public function testTrigger(): void
    {
        $message = $this->faker->sentence();

        // E_ERROR must throw instead of just triggering a PHP error
        try
        {
            $this->silently(fn () => Exceptions::trigger($message, Exceptions::E_ERROR));
            $this->fail('Expected an Exception to be thrown for E_ERROR.');
        }
        catch (Exception $e)
        {
            $this->assertSame($message, $e->getMessage());
        }

        // every non-error type maps to a trigger_error() call and returns true
        foreach ([Exceptions::E_WARNING, Exceptions::E_NOTICE, Exceptions::E_DEPRECATED, Exceptions::E_STRICT, Exceptions::E_UNKNOWN] as $type)
        {
            $result = $this->silently(fn () => Exceptions::trigger($message, $type));

            $this->assertTrue($result);
        }
    }

    public function testError(): void
    {
        $message = $this->faker->sentence();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $this->silently(fn () => Exceptions::error($message));
    }

    public function testWarning(): void
    {
        $this->assertTrue($this->silently(fn () => Exceptions::warning($this->faker->sentence())));
    }

    public function testNotice(): void
    {
        $this->assertTrue($this->silently(fn () => Exceptions::notice($this->faker->sentence())));
    }

    public function testDeprecated(): void
    {
        $this->assertTrue($this->silently(fn () => Exceptions::deprecated($this->faker->sentence())));
    }

    public function testStrict(): void
    {
        $this->assertTrue($this->silently(fn () => Exceptions::strict($this->faker->sentence())));
    }

    public function testUnknown(): void
    {
        $this->assertTrue($this->silently(fn () => Exceptions::unknown($this->faker->sentence())));
    }
}

<?php

namespace Stackstra\Tests\Curl;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Curl\CurlOptions;
use Stackstra\Tests\TestCase;

#[CoversClass(CurlOptions::class)]
class CurlOptionsTest extends TestCase
{
    public function testConstruct(): void
    {
        // no options: every default applies
        $options = new CurlOptions();

        $this->assertFalse($options->progress());
        $this->assertSame(1, $options->threads());
        $this->assertSame(1, $options->max_attempts());
        $this->assertNull($options->throttle_slots());
        $this->assertNull($options->throttle_interval());
        $this->assertTrue($options->remember_responses());

        // explicit options override only the given keys, defaults fill the rest
        $options = new CurlOptions([CurlOptions::OPTION_THREADS => 5]);

        $this->assertSame(5, $options->threads());
        $this->assertSame(1, $options->max_attempts());
    }

    public function testSet(): void
    {
        $options = new CurlOptions();

        $result = $options->set([CurlOptions::OPTION_THREADS => 3]);

        $this->assertSame($options, $result);
        $this->assertSame(3, $options->threads());

        // unrelated existing options are preserved
        $this->assertSame(1, $options->max_attempts());
    }

    public function testProgress(): void
    {
        $options = new CurlOptions();

        // getter (no argument): the default
        $this->assertFalse($options->progress());

        // setter
        $options->progress(true);
        $this->assertTrue($options->progress());
    }

    public function testThreads(): void
    {
        $options = new CurlOptions();

        $this->assertSame(1, $options->threads());

        $options->threads(4);
        $this->assertSame(4, $options->threads());
    }

    public function testMaxAttempts(): void
    {
        $options = new CurlOptions();

        $this->assertSame(1, $options->max_attempts());

        $options->max_attempts(3);
        $this->assertSame(3, $options->max_attempts());
    }

    public function testThrottleSlots(): void
    {
        $options = new CurlOptions();

        $this->assertNull($options->throttle_slots());

        $options->throttle_slots(10);
        $this->assertSame(10, $options->throttle_slots());
    }

    public function testThrottleInterval(): void
    {
        $options = new CurlOptions();

        $this->assertNull($options->throttle_interval());

        $options->throttle_interval(60);
        $this->assertSame(60, $options->throttle_interval());
    }

    public function testRememberResponses(): void
    {
        $options = new CurlOptions();

        $this->assertTrue($options->remember_responses());

        $options->remember_responses(false);
        $this->assertFalse($options->remember_responses());
    }
}

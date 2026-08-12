<?php

namespace Stackstra\Tests;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static ?Generator $sharedFaker = null;

    protected Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = self::$sharedFaker ??= Factory::create();
    }

    /**
     * Runs $callback with error_log()/trigger_error() output swallowed, so that
     * exercising a code path that intentionally logs/warns doesn't mark the test risky.
     */
    protected function silently(callable $callback): mixed
    {
        ini_set('error_log', sys_get_temp_dir() . '/stackstra-tests-error.log');

        ob_start();

        try
        {
            return @$callback();
        }
        finally
        {
            ob_end_clean();
        }
    }
}

<?php

namespace Stackstra\Tests\Singleton;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Singleton\Singleton;
use Stackstra\Tests\TestCase;

#[CoversClass(Singleton::class)]
class SingletonTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = sys_get_temp_dir() . '/' . $this->faker->uuid() . '.lock';
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
    }

    public function testLock(): void
    {
        // file does not exist yet, lock() must create it and then succeed
        $this->assertFileDoesNotExist($this->path);

        $singleton = new Singleton($this->path);

        $this->assertTrue($singleton->lock());
        $this->assertFileExists($this->path);

        // a second lock, on an already-existing file, also succeeds (shared, non-blocking)
        $second = new Singleton($this->path);

        $this->assertTrue($second->lock());
    }

    public function testUnlock(): void
    {
        $singleton = new Singleton($this->path);
        $singleton->lock();

        // unlock() has no return value to assert on; it must simply not throw
        $singleton->unlock();

        $this->assertTrue(true);
    }
}

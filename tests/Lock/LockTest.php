<?php

namespace Stackstra\Tests\Lock;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Lock\Lock;
use Stackstra\Tests\TestCase;

#[CoversClass(Lock::class)]
class LockTest extends TestCase
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

    public function testConstruct(): void
    {
        // explicit path: lock() creates that exact file
        $lock = new Lock($this->path);
        $this->assertFileDoesNotExist($this->path);
        $lock->lock();
        $this->assertFileExists($this->path);

        // path omitted: a fresh temp file is used instead
        $auto = new Lock();
        $this->assertTrue($auto->lock());
    }

    public function testLock(): void
    {
        $lock = new Lock($this->path);

        // first lock succeeds and writes this process's PID into the file
        $this->assertTrue($lock->lock());
        $this->assertTrue($lock->is_locked());
        $this->assertSame((string) getmypid(), file_get_contents($this->path));

        // a second Lock instance on the same still-running-PID file is rejected outright
        $second = new Lock($this->path);
        $this->assertFalse($second->lock());
    }

    public function testUnlock(): void
    {
        $lock = new Lock($this->path);
        $lock->lock();

        $this->assertTrue($lock->unlock());
        $this->assertFalse($lock->is_locked());

        // unlocking an already-unlocked instance is a no-op that returns false
        $this->assertFalse($lock->unlock());
    }

    public function testIsLocked(): void
    {
        $lock = new Lock($this->path);

        $this->assertFalse($lock->is_locked());

        $lock->lock();
        $this->assertTrue($lock->is_locked());

        $lock->unlock();
        $this->assertFalse($lock->is_locked());
    }

    public function testCreate(): void
    {
        $lock = new Lock($this->path);

        // no argument: uses the instance's own path
        $this->assertFileDoesNotExist($this->path);
        $this->assertTrue($lock->create());
        $this->assertFileExists($this->path);

        // explicit path argument overrides the instance's path
        $other = sys_get_temp_dir() . '/' . $this->faker->uuid() . '.lock';
        $this->assertTrue($lock->create($other));
        $this->assertFileExists($other);
        @unlink($other);
    }

    public function testTruncate(): void
    {
        $lock = new Lock($this->path);
        $lock->lock(); // opens the file handle and writes the PID

        clearstatcache(true, $this->path);

        $this->assertGreaterThan(0, filesize($this->path));

        $this->assertTrue($lock->truncate());

        clearstatcache(true, $this->path);

        $this->assertSame(0, filesize($this->path));
    }

    public function testWrite(): void
    {
        $lock = new Lock($this->path);
        $lock->lock();
        $lock->truncate();

        $bytes = $lock->write('hello');

        $this->assertSame(5, $bytes);
        $this->assertSame('hello', file_get_contents($this->path));
    }

    public function testDelete(): void
    {
        $lock = new Lock($this->path);
        $lock->lock();

        $this->assertTrue($lock->delete());
        $this->assertFileDoesNotExist($this->path);

        // nothing left to delete: file handle is null and the path no longer exists
        $this->assertFalse($lock->delete());
    }
}

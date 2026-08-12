<?php

namespace Stackstra\Tests\Filesystem\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Filesystem\Traits\HasPath;
use Stackstra\Tests\TestCase;

#[CoversClass(HasPath::class)]
class HasPathTest extends TestCase
{
    private string $dir;

    private object $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/' . $this->faker->uuid();

        mkdir($this->dir);

        $this->subject = new class ($this->dir . '/file.txt') {
            use HasPath;

            public function __construct(string $path) { $this->path = $path; }
            public function isExist(): bool { return file_exists($this->path); }
            public function create(): bool { return touch($this->path); }
        };
    }

    protected function tearDown(): void
    {
        \Stackstra\Filesystem\Directory::remove($this->dir, true);
    }

    public function testPath(): void
    {
        $this->assertSame($this->dir . '/file.txt', $this->subject->path());
    }

    public function testDirectory(): void
    {
        $this->assertSame($this->dir, $this->subject->directory());
    }

    public function testExtension(): void
    {
        $this->assertSame('txt', $this->subject->extension());
    }

    public function testName(): void
    {
        $this->assertSame('file.txt', $this->subject->name());
        $this->assertSame('file', $this->subject->name(false));
    }

    public function testSize(): void
    {
        $this->subject->create();
        file_put_contents($this->subject->path(), 'hello');

        $this->assertSame(5, $this->subject->size());

        // missing file: null instead of false
        $missing = new class ($this->dir . '/missing.txt') {
            use HasPath;

            public function __construct(string $path) { $this->path = $path; }
            public function isExist(): bool { return file_exists($this->path); }
            public function create(): bool { return touch($this->path); }
        };

        $this->assertNull(@$missing->size());
    }
}

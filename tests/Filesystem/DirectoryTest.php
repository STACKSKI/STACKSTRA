<?php

namespace Stackstra\Tests\Filesystem;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\OS;
use Stackstra\Filesystem\Directory;
use Stackstra\Tests\TestCase;

#[CoversClass(Directory::class)]
class DirectoryTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/' . $this->faker->uuid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dir))
        {
            Directory::remove($this->dir, true);
        }
    }

    public function testCurrent(): void
    {
        $this->assertSame(getcwd(), Directory::current());
    }

    public function testChange(): void
    {
        $original = getcwd();

        $this->assertTrue(Directory::change($this->dir));
        $this->assertSame(realpath($this->dir), realpath(getcwd()));

        chdir($original);
    }

    public function testTmp(): void
    {
        $this->assertSame(sys_get_temp_dir(), Directory::tmp());
    }

    public function testFiles(): void
    {
        touch($this->dir . '/a.txt');
        touch($this->dir . '/b.txt');
        mkdir($this->dir . '/sub');

        // dot/dot-dot entries excluded, everything else included
        $files = Directory::files($this->dir);
        sort($files);
        $this->assertSame(['a.txt', 'b.txt', 'sub'], $files);
    }

    public function testCreate(): void
    {
        $path = $this->dir . '/new';

        // default: non-recursive, fails if a nested parent is missing
        $this->assertTrue(Directory::create($path));
        $this->assertDirectoryExists($path);

        // already exists, skip_if_exists=false (default): mkdir() fails
        $this->assertFalse(@Directory::create($path));

        // already exists, skip_if_exists=true: treated as success
        $this->assertTrue(Directory::create($path, skip_if_exists: true));

        // recursive=true creates missing intermediate directories
        $nested = $this->dir . '/a/b/c';
        $this->assertTrue(Directory::create($nested, recursive: true));
        $this->assertDirectoryExists($nested);
    }

    public function testCreateOrFail(): void
    {
        $path = $this->dir . '/new';

        $this->assertTrue(Directory::createOrFail($path));
        $this->assertDirectoryExists($path);

        // already exists, skip_if_exists=false: throws instead of silently failing
        $this->expectException(\Exception::class);
        $this->silently(fn () => Directory::createOrFail($path));
    }

    public function testRemove(): void
    {
        $path = $this->dir . '/empty';
        mkdir($path);

        // non-recursive: only works on an empty directory
        $this->assertTrue(Directory::remove($path));
        $this->assertDirectoryDoesNotExist($path);

        // recursive=true removes non-empty directories, files and all
        $path = $this->dir . '/full';
        mkdir($path);
        mkdir($path . '/sub');
        touch($path . '/sub/file.txt');

        $this->assertTrue(Directory::remove($path, true));
        $this->assertDirectoryDoesNotExist($path);
    }

    public function testSize(): void
    {
        mkdir($this->dir . '/sub');
        file_put_contents($this->dir . '/a.txt', '12345');
        file_put_contents($this->dir . '/sub/b.txt', '1234567890');

        $this->assertSame(15, Directory::size($this->dir));
    }

    public function testIsExist(): void
    {
        $this->assertTrue(Directory::isExist($this->dir));
        $this->assertFalse(Directory::isExist($this->dir . '/missing'));

        // a file is not a directory
        touch($this->dir . '/file.txt');
        $this->assertFalse(Directory::isExist($this->dir . '/file.txt'));
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(Directory::isEmpty($this->dir));

        touch($this->dir . '/file.txt');
        $this->assertFalse(Directory::isEmpty($this->dir));
    }

    public function testAssertExists(): void
    {
        // no exception when it exists
        Directory::assertExists($this->dir);
        $this->assertTrue(true);

        $this->expectException(\Exception::class);
        $this->silently(fn () => Directory::assertExists($this->dir . '/missing'));
    }

    public function testName(): void
    {
        $this->assertSame(basename($this->dir), Directory::name($this->dir . '/'));
    }

    public function testParentName(): void
    {
        $this->assertSame(dirname($this->dir), Directory::parentName($this->dir));
    }

    public function testMove(): void
    {
        $dest = $this->dir . '-moved';

        $this->assertTrue(Directory::move($this->dir, $dest));
        $this->assertDirectoryExists($dest);
        $this->assertDirectoryDoesNotExist($this->dir);

        Directory::remove($dest);

        // source doesn't exist: false, no exception
        $this->assertFalse(Directory::move($this->dir, $dest));
    }

    public function testSpaceTotal(): void
    {
        $this->assertIsFloat(Directory::spaceTotal($this->dir));
        $this->assertGreaterThan(0, Directory::spaceTotal($this->dir));
    }

    public function testSpaceFree(): void
    {
        $this->assertIsFloat(Directory::spaceFree($this->dir));
        $this->assertGreaterThanOrEqual(0, Directory::spaceFree($this->dir));
    }

    public function testSpaceUsed(): void
    {
        $used = Directory::spaceUsed($this->dir);

        $this->assertSame(Directory::spaceTotal($this->dir) - Directory::spaceFree($this->dir), $used);
    }

    public function testPathCombine(): void
    {
        // nested arrays are flattened, separators trimmed from each segment
        $this->assertSame('/a/b/c', Directory::pathCombine('/a/', ['b/', 'c']));

        // null entries are dropped entirely
        $this->assertSame('/a/c', Directory::pathCombine('a', null, 'c'));

        // on Linux/BSD, a leading separator is always enforced
        if (OS::IS_LINUX || OS::IS_BSD)
        {
            $this->assertSame('/a/b', Directory::pathCombine('a', 'b'));
        }
    }

    public function testClearCaches(): void
    {
        // no return value to assert on; it must simply not throw
        Directory::clearCaches();
        $this->assertTrue(true);
    }
}

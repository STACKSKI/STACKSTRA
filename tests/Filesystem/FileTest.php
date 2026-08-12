<?php

namespace Stackstra\Tests\Filesystem;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Filesystem\Directory;
use Stackstra\Filesystem\File;
use Stackstra\Filesystem\FileObject;
use Stackstra\Tests\TestCase;

#[CoversClass(File::class)]
class FileTest extends TestCase
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
        Directory::remove($this->dir, true);
    }

    private function path(string $name = 'file.txt'): string
    {
        return $this->dir . '/' . $name;
    }

    public function testOpen(): void
    {
        $file = File::open($this->path(), 'w');

        $this->assertInstanceOf(FileObject::class, $file);
        $this->assertSame('w', $file->mode());
    }

    public function testCreate(): void
    {
        $path = $this->path();

        $this->assertFileDoesNotExist($path);
        $this->assertTrue(File::create($path));
        $this->assertFileExists($path);
    }

    public function testRead(): void
    {
        $path = $this->path();
        file_put_contents($path, 'hello');

        $this->assertSame('hello', File::read($path));

        // missing file: null instead of throwing
        $this->assertNull(@File::read($this->path('missing.txt')));
    }

    public function testWrite(): void
    {
        $path = $this->path();

        // default flags: overwrite
        $this->assertSame(5, File::write($path, 'hello'));
        $this->assertSame('hello', file_get_contents($path));

        $this->assertSame(2, File::write($path, 'hi'));
        $this->assertSame('hi', file_get_contents($path));

        // FILE_APPEND flag appends instead of overwriting
        File::write($path, '!', FILE_APPEND);
        $this->assertSame('hi!', file_get_contents($path));

        // recursive=true creates missing parent directories first
        $nested = $this->dir . '/a/b/c.txt';
        $this->assertNull(@File::write($nested, 'x')); // parent missing, recursive=false: fails
        $this->assertSame(1, File::write($nested, 'x', recursive: true));
        $this->assertFileExists($nested);
    }

    public function testRewrite(): void
    {
        $path = $this->path();
        file_put_contents($path, 'old content');

        File::rewrite($path, 'new');

        $this->assertSame('new', file_get_contents($path));
    }

    public function testAppend(): void
    {
        $path = $this->path();
        file_put_contents($path, 'a');

        File::append($path, 'b');

        $this->assertSame('ab', file_get_contents($path));
    }

    public function testAppendLine(): void
    {
        $path = $this->path();
        file_put_contents($path, 'a');

        File::appendLine($path, 'b');

        $this->assertSame('a' . 'b' . PHP_EOL, file_get_contents($path));
    }

    public function testPathReal(): void
    {
        $path = $this->path();
        file_put_contents($path, 'x');

        $this->assertSame(realpath($path), File::pathReal($path));

        // non-existent path: null instead of false
        $this->assertNull(File::pathReal($this->path('missing.txt')));
    }

    public function testPathCombine(): void
    {
        // trailing/leading separators and leading dots on later segments are trimmed
        $this->assertSame('/a/b/c', File::pathCombine('/a/', '/b/', './c'));

        // null arguments are skipped entirely
        $this->assertSame('/a/c', File::pathCombine('/a/', null, 'c'));

        // first argument only trims the right side, so a bare leading segment stays intact
        $this->assertSame('a/b', File::pathCombine('a', 'b'));
    }

    public function testCreateTmp(): void
    {
        $path = File::createTmp('stackstra_test_');

        $this->assertNotNull($path);
        $this->assertFileExists($path);
        $this->assertStringContainsString('stackstra_test_', basename($path));

        unlink($path);
    }

    public function testIsExist(): void
    {
        $path = $this->path();

        $this->assertFalse(File::isExist($path));

        file_put_contents($path, 'x');
        $this->assertTrue(File::isExist($path));

        // a directory is not a file
        $this->assertFalse(File::isExist($this->dir));
    }

    public function testIsLink(): void
    {
        $path = $this->path();
        file_put_contents($path, 'x');
        $link = $this->path('link.txt');
        symlink($path, $link);

        $this->assertTrue(File::isLink($link));
        $this->assertFalse(File::isLink($path));
    }

    public function testTimestamp(): void
    {
        $path = $this->path();
        file_put_contents($path, 'x');

        $this->assertSame(filemtime($path), File::timestamp($path));

        // missing file: null instead of false
        $this->assertNull(@File::timestamp($this->path('missing.txt')));
    }

    public function testParts(): void
    {
        $path = '/a/b/file.txt';

        // no $part: full pathinfo() array
        $this->assertSame(pathinfo($path), File::parts($path));

        // explicit $part: just that piece
        $this->assertSame('txt', File::parts($path, PATHINFO_EXTENSION));
        $this->assertSame('file', File::parts($path, PATHINFO_FILENAME));
    }

    public function testDirectory(): void
    {
        $this->assertSame('/a/b', File::directory('/a/b/file.txt'));
    }

    public function testExtension(): void
    {
        $this->assertSame('txt', File::extension('/a/b/file.txt'));

        // no extension: empty string
        $this->assertSame('', File::extension('/a/b/file'));
    }

    public function testName(): void
    {
        $this->assertSame('file.txt', File::name('/a/b/file.txt'));

        // include_extension=false: bare filename
        $this->assertSame('file', File::name('/a/b/file.txt', false));
    }

    public function testSize(): void
    {
        $path = $this->path();
        file_put_contents($path, 'hello');

        $this->assertSame(5, File::size($path));

        // missing file: null instead of false
        $this->assertNull(@File::size($this->path('missing.txt')));
    }

    public function testMime(): void
    {
        $path = $this->path();
        file_put_contents($path, 'plain text content');

        $this->assertSame('text/plain', File::mime($path));
    }

    public function testChangeExtension(): void
    {
        $path = $this->path('file.txt');
        file_put_contents($path, 'x');

        $this->assertTrue(File::changeExtension($path, 'md'));
        $this->assertFileExists($this->path('file.md'));
        $this->assertFileDoesNotExist($path);
    }

    public function testChangeName(): void
    {
        $path = $this->path('file.txt');
        file_put_contents($path, 'x');

        $this->assertTrue(File::changeName($path, 'renamed.txt'));
        $this->assertFileExists($this->path('renamed.txt'));
        $this->assertFileDoesNotExist($path);
    }

    public function testMove(): void
    {
        $path = $this->path('file.txt');
        $dest = $this->path('moved.txt');
        file_put_contents($path, 'x');

        $this->assertTrue(File::move($path, $dest));
        $this->assertFileExists($dest);

        // source doesn't exist: false, no exception
        $this->assertFalse(File::move($path, $dest));
    }

    public function testRename(): void
    {
        $path = $this->path('file.txt');
        file_put_contents($path, 'x');

        $this->assertTrue(File::rename($path, 'renamed.txt'));
        $this->assertFileExists($this->path('renamed.txt'));
    }

    public function testCopy(): void
    {
        $path = $this->path('file.txt');
        $dest = $this->path('copy.txt');
        file_put_contents($path, 'x');

        $this->assertTrue(File::copy($path, $dest));
        $this->assertFileExists($path);
        $this->assertFileExists($dest);
    }

    public function testRemove(): void
    {
        $path = $this->path();
        file_put_contents($path, 'x');

        $this->assertTrue(File::remove($path));
        $this->assertFileDoesNotExist($path);
    }

    public function testHashSHA1(): void
    {
        $path = $this->path();
        file_put_contents($path, 'hello');

        $this->assertSame(sha1('hello'), File::hashSHA1($path));
    }

    public function testHashMD5(): void
    {
        $path = $this->path();
        file_put_contents($path, 'hello');

        $this->assertSame(md5('hello'), File::hashMD5($path));
    }

    public function testHashCRC32(): void
    {
        $path = $this->path();
        file_put_contents($path, 'hello');

        $this->assertSame(crc32('hello'), File::hashCRC32($path));
    }
}

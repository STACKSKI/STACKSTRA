<?php

namespace Stackstra\Tests\Filesystem;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Filesystem\Directory;
use Stackstra\Filesystem\DirectoryObject;
use Stackstra\Filesystem\FileObject;
use Stackstra\Tests\TestCase;

#[CoversClass(DirectoryObject::class)]
class DirectoryObjectTest extends TestCase
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

    public function testConstruct(): void
    {
        $directory = new DirectoryObject($this->dir);

        $this->assertSame($this->dir, $directory->path());
    }

    public function testMake(): void
    {
        // string path, format=false (default)
        $directory = DirectoryObject::make($this->dir);

        $this->assertSame($this->dir, $directory->path());

        // array path: combined into a single path
        $directory = DirectoryObject::make([$this->dir, 'sub']);

        $this->assertSame($this->dir . '/sub', $directory->path());

        // format=true: markers in the path get resolved
        $directory = DirectoryObject::make($this->dir . '/{DATE}', format: true);

        $this->assertStringNotContainsString('{DATE}', $directory->path());
    }

    public function testMarkers(): void
    {
        $markers = DirectoryObject::markers();

        $this->assertArrayHasKey('DIR', $markers);
        $this->assertSame(DirectoryObject::MARKER_DIR, $markers['DIR']);

        $this->assertArrayHasKey('DATE', $markers);
        $this->assertArrayHasKey('DATE_TIME', $markers);
    }

    public function testGet(): void
    {
        $directory = new DirectoryObject($this->dir);

        // an unknown property name: treated as a plain path segment, no formatting applied
        $child = $directory->not_a_marker;

        $this->assertSame($this->dir . '/not_a_marker', $child->path());

        // a known marker: resolved via format()
        $dated = $directory->DATE;

        $this->assertStringNotContainsString('{DATE}', $dated->path());
    }

    public function testFile(): void
    {
        $directory = new DirectoryObject($this->dir);

        $file = $directory->file('test.txt');

        $this->assertInstanceOf(FileObject::class, $file);
        $this->assertSame($this->dir . '/test.txt', $file->path());
        $this->assertFalse($file->isOpened());

        // explicit mode opens it immediately
        touch($file->path());

        $opened = $directory->file('test.txt', 'r');

        $this->assertTrue($opened->isOpened());
    }

    public function testFileDateTime(): void
    {
        $directory = new DirectoryObject($this->dir);

        $file = $directory->fileDateTime(extension: '.log');

        $this->assertStringEndsWith('.log', $file->path());
        $this->assertMatchesRegularExpression('/\d{8}\d{6}\.log$/', $file->path());
    }

    public function testFileMostRecent(): void
    {
        // despite the name, this sorts by filename (natural, descending) — not by mtime
        $directory = new DirectoryObject($this->dir);

        touch($this->dir . '/a.txt');
        touch($this->dir . '/z.txt');

        $file = $directory->fileMostRecent();

        $this->assertSame($this->dir . '/z.txt', $file->path());
    }

    public function testAll(): void
    {
        touch($this->dir . '/a.txt');
        mkdir($this->dir . '/sub');

        $directory = new DirectoryObject($this->dir);

        $results = $directory->all();

        $this->assertCount(2, $results);
    }

    public function testDirectories(): void
    {
        touch($this->dir . '/a.txt');
        mkdir($this->dir . '/sub');

        $directory = new DirectoryObject($this->dir);

        $results = $directory->directories();

        $this->assertCount(1, $results);
        $this->assertInstanceOf(DirectoryObject::class, $results[0]);
    }

    public function testFiles(): void
    {
        touch($this->dir . '/a.txt');
        touch($this->dir . '/b.log');
        mkdir($this->dir . '/sub');

        $directory = new DirectoryObject($this->dir);

        // no pattern: all files, no subdirectories
        $results = $directory->files();

        $this->assertCount(2, $results);

        // explicit pattern filters by glob
        $results = $directory->files('*.txt');

        $this->assertCount(1, $results);
        $this->assertSame($this->dir . '/a.txt', $results[0]->path());
    }

    public function testUp(): void
    {
        $directory = new DirectoryObject($this->dir . '/a/b');

        $parent = $directory->up();

        $this->assertSame($this->dir . '/a', $parent->path());

        $grandparent = $directory->up(2);

        $this->assertSame($this->dir, $grandparent->path());
    }

    public function testDown(): void
    {
        $directory = new DirectoryObject($this->dir);

        // string
        $child = $directory->down('sub');

        $this->assertSame($this->dir . '/sub', $child->path());

        // array
        $child = $directory->down(['sub', 'deeper']);

        $this->assertSame($this->dir . '/sub/deeper', $child->path());
    }

    public function testCreate(): void
    {
        $directory = new DirectoryObject($this->dir . '/a/b');

        // recursive=true (default) creates intermediate directories
        $this->assertTrue($directory->create());
        $this->assertDirectoryExists($this->dir . '/a/b');
    }

    public function testCreateIfNotExist(): void
    {
        $directory = new DirectoryObject($this->dir . '/sub');

        // returns self for chaining
        $result = $directory->createIfNotExist();

        $this->assertSame($directory, $result);
        $this->assertDirectoryExists($this->dir . '/sub');

        // already exists: no error on the second call
        $directory->createIfNotExist();

        $this->assertDirectoryExists($this->dir . '/sub');
    }

    public function testIsExist(): void
    {
        $directory = new DirectoryObject($this->dir);

        $this->assertTrue($directory->isExist());

        $missing = new DirectoryObject($this->dir . '/missing');

        $this->assertFalse($missing->isExist());
    }

    public function testDelete(): void
    {
        mkdir($this->dir . '/empty');

        $directory = new DirectoryObject($this->dir . '/empty');

        $this->assertTrue($directory->delete());
        $this->assertDirectoryDoesNotExist($this->dir . '/empty');

        // recursively=true removes non-empty directories
        mkdir($this->dir . '/full');
        touch($this->dir . '/full/file.txt');

        $directory = new DirectoryObject($this->dir . '/full');

        $this->assertTrue($directory->delete(true));
        $this->assertDirectoryDoesNotExist($this->dir . '/full');
    }

    public function testMove(): void
    {
        $directory = new DirectoryObject($this->dir);
        $dest      = $this->dir . '-moved';

        $result = $directory->move($dest);

        $this->assertInstanceOf(DirectoryObject::class, $result);
        $this->assertDirectoryExists($dest);
        $this->assertDirectoryDoesNotExist($this->dir);

        Directory::remove($dest);
    }

    public function testFormat(): void
    {
        // no markers: unchanged
        $this->assertSame('/plain/path', DirectoryObject::format('/plain/path'));

        // custom variable substitution
        $this->assertSame('/a/value/c', DirectoryObject::format('/a/{VAR}/c', ['var' => 'value']));

        // {DATE} / {DATE_TIME} markers are replaced with the current date/datetime
        $this->assertDoesNotMatchRegularExpression('/\{DATE\}/', DirectoryObject::format('{DATE}'));
        $this->assertDoesNotMatchRegularExpression('/\{DATE_TIME\}/', DirectoryObject::format('{DATE_TIME}'));
    }

    public function testRewindCurrentKeyNextValid(): void
    {
        // Iterator support only walks subdirectories (Iterator delegates to directories())
        touch($this->dir . '/a.txt');
        mkdir($this->dir . '/sub1');
        mkdir($this->dir . '/sub2');

        $directory = new DirectoryObject($this->dir);

        $items = [];

        foreach ($directory as $key => $value)
        {
            $items[$key] = $value;
        }

        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(DirectoryObject::class, $items);

        // a second full iteration re-derives from the same cached list
        $items2 = [];

        foreach ($directory as $value)
        {
            $items2[] = $value;
        }

        $this->assertCount(2, $items2);
    }

    public function testSearch(): void
    {
        $directory = new DirectoryObject($this->dir);

        $search = $directory->search();

        $this->assertSame($this->dir, $search->path());
    }
}

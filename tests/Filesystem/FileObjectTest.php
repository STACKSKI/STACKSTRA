<?php

namespace Stackstra\Tests\Filesystem;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Filesystem\Directory;
use Stackstra\Filesystem\DirectoryObject;
use Stackstra\Filesystem\FileObject;
use Stackstra\Tests\TestCase;

#[CoversClass(FileObject::class)]
class FileObjectTest extends TestCase
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

    public function testConstruct(): void
    {
        // mode omitted: no file handle opened
        $file = new FileObject($this->path());
        $this->assertFalse($file->isOpened());
        $this->assertNull($file->mode());

        // explicit mode: opened immediately
        touch($this->path());
        $file = new FileObject($this->path(), 'r');
        $this->assertTrue($file->isOpened());
        $this->assertSame('r', $file->mode());

        // array path: combined via File::pathCombine()
        $file = FileObject::make([$this->dir, 'sub', 'file.txt']);
        $this->assertSame($this->dir . '/sub/file.txt', $file->path());
    }

    public function testMake(): void
    {
        // string path
        $file = FileObject::make($this->path());
        $this->assertInstanceOf(FileObject::class, $file);

        // an existing FileObject instance, mode omitted: passed through unchanged, still closed
        $same = FileObject::make($file);
        $this->assertSame($file, $same);
        $this->assertFalse($same->isOpened());

        // an existing FileObject instance, mode provided: opens it in place
        touch($this->path());
        $reopened = FileObject::make($file, 'r');
        $this->assertSame($file, $reopened);
        $this->assertTrue($reopened->isOpened());
    }

    public function testGet(): void
    {
        $file = new FileObject($this->path());
        $this->assertNull($file->get());

        touch($this->path());
        $file->open('r');
        $this->assertInstanceOf(\SplFileObject::class, $file->get());
    }

    public function testOpen(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        $file->open('r');

        $this->assertTrue($file->isOpened());
        $this->assertSame('r', $file->mode());
    }

    public function testOpenIfClosed(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        // closed: opens with the given mode
        $file->openIfClosed('r');
        $this->assertSame('r', $file->mode());

        // already opened: mode argument is ignored entirely
        $file->openIfClosed('w');
        $this->assertSame('r', $file->mode());
    }

    public function testReopen(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path(), 'r');

        // mode omitted: reuses the current mode
        $this->assertTrue($file->reopen());
        $this->assertSame('r', $file->mode());

        // explicit mode: switches
        $this->assertTrue($file->reopen('r+'));
        $this->assertSame('r+', $file->mode());
    }

    public function testClose(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $file->close();

        $this->assertFalse($file->isOpened());
        $this->assertNull($file->mode());
    }

    public function testMode(): void
    {
        $file = new FileObject($this->path());
        $this->assertNull($file->mode());

        touch($this->path());
        $file->open('r+');
        $this->assertSame('r+', $file->mode());
    }

    public function testResize(): void
    {
        file_put_contents($this->path(), 'hello world');
        $file = new FileObject($this->path(), 'r+');

        $this->assertTrue($file->resize(5));

        clearstatcache(true, $this->path());
        $this->assertSame(5, filesize($this->path()));
    }

    public function testTruncate(): void
    {
        file_put_contents($this->path(), 'hello world');
        $file = new FileObject($this->path(), 'r+');

        // default size=0
        $this->assertTrue($file->truncate());

        clearstatcache(true, $this->path());
        $this->assertSame(0, filesize($this->path()));

        // explicit size
        file_put_contents($this->path(), 'hello world');
        $file = new FileObject($this->path(), 'r+');
        $file->truncate(5);

        clearstatcache(true, $this->path());
        $this->assertSame(5, filesize($this->path()));
    }

    public function testFlush(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'w');
        $file->write('x');

        $this->assertTrue($file->flush());
    }

    public function testGetStat(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path(), 'r');

        $stat = $file->getStat();

        $this->assertIsArray($stat);
        $this->assertSame(5, $stat['size']);
    }

    public function testLockForRead(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        // blocking (default) and non-blocking variants both succeed here (no contention)
        $this->assertTrue($file->lockForRead());
        $file->unlock();
        $this->assertTrue($file->lockForRead(true));
    }

    public function testLockForWrite(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r+');

        $this->assertTrue($file->lockForWrite());
        $file->unlock();
        $this->assertTrue($file->lockForWrite(true));
    }

    public function testUnlock(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r+');
        $file->lockForWrite();

        $this->assertTrue($file->unlock());
    }

    public function testCreate(): void
    {
        $file = new FileObject($this->path());

        $this->assertFileDoesNotExist($this->path());
        $this->assertTrue($file->create());
        $this->assertFileExists($this->path());
    }

    public function testCreateIfNotExist(): void
    {
        $file = new FileObject($this->path());

        $file->createIfNotExist();
        $this->assertFileExists($this->path());

        $before = filemtime($this->path());
        sleep(0); // no-op, keep intent explicit: mtime must NOT be touched again below
        $file->createIfNotExist(); // already exists: create() is skipped
        $this->assertSame($before, filemtime($this->path()));
    }

    public function testWrite(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'w');

        $this->assertSame(5, $file->write('hello'));

        // explicit length truncates what gets written
        $file2 = new FileObject($this->path(), 'w');
        $this->assertSame(2, $file2->write('hello', 2));
    }

    public function testWriteJSON(): void
    {
        $file = new FileObject($this->path(), 'w');

        $file->writeJSON(['a' => 1]);

        $this->assertSame(json_encode(['a' => 1], JSON_PRETTY_PRINT), file_get_contents($this->path()));
    }

    public function testWriteLine(): void
    {
        $file = new FileObject($this->path(), 'w');

        $file->writeLine('hello');

        $this->assertSame('hello' . PHP_EOL, file_get_contents($this->path()));
    }

    public function testRead(): void
    {
        file_put_contents($this->path(), 'hello world');
        $file = new FileObject($this->path(), 'r');

        // length omitted: reads the whole file
        $this->assertSame('hello world', $file->read());

        // explicit length
        $file = new FileObject($this->path(), 'r');
        $this->assertSame('hello', $file->read(5));
    }

    public function testReadJSON(): void
    {
        file_put_contents($this->path(), json_encode(['a' => 1]));
        $file = new FileObject($this->path(), 'r');

        // associative=true (default)
        $this->assertSame(['a' => 1], $file->readJSON());

        // associative=false: stdClass
        $file = new FileObject($this->path(), 'r');
        $result = $file->readJSON(false);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertSame(1, $result->a);

        // empty file: null
        touch($this->path('empty.json'));
        $empty = new FileObject($this->path('empty.json'), 'r');
        $this->assertNull($empty->readJSON());
    }

    public function testReadCSV(): void
    {
        file_put_contents($this->path(), "id,name\n1,alice\n2,bob\n");
        $file = new FileObject($this->path(), 'r');

        // no column mapping: raw rows, first row (header) included as data
        $rows = $file->readCSV();
        $this->assertSame(['id', 'name'], $rows[0]);
        $this->assertSame(['1', 'alice'], $rows[1]);

        // skip_headers=true drops the first row
        $file = new FileObject($this->path(), 'r');
        $rows = $file->readCSV(skip_headers: true);
        $this->assertSame(['1', 'alice'], $rows[0]);

        // column remap: keep only named columns, keyed by name
        $file = new FileObject($this->path(), 'r');
        $rows = $file->readCSV(columns_all: ['id', 'name'], columns_keep: ['name'], skip_headers: true);
        $this->assertSame(['name' => 'alice'], $rows[0]);

        // index argument: keys the result by a column's value
        $file = new FileObject($this->path(), 'r');
        $rows = $file->readCSV(columns_all: ['id', 'name'], skip_headers: true, index: 'id');
        $this->assertArrayHasKey('1', $rows);
    }

    public function testReadChar(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path(), 'r');

        $this->assertSame('h', $file->readChar());
    }

    public function testReadLine(): void
    {
        file_put_contents($this->path(), "line1\nline2\n");
        $file = new FileObject($this->path(), 'r');

        $this->assertSame("line1\n", $file->readLine());
        $this->assertSame("line2\n", $file->readLine());
    }

    public function testOffsetGet(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path(), 'r');

        $this->assertSame(0, $file->offsetGet());

        $file->readChar();
        $this->assertSame(1, $file->offsetGet());
    }

    public function testOffsetSet(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path(), 'r');

        $this->assertTrue($file->offsetSet(2));
        $this->assertSame('l', $file->readChar());
    }

    public function testOffsetSetOEF(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path(), 'r');

        $this->assertTrue($file->offsetSetOEF());
        $this->assertSame(5, $file->offsetGet());
    }

    public function testOffsetReset(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path(), 'r');
        $file->offsetSet(3);

        $file->offsetReset();

        $this->assertSame(0, $file->offsetGet());
    }

    public function testRemove(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertTrue($file->remove());
        $this->assertFileDoesNotExist($this->path());
        $this->assertFalse($file->isOpened()); // closed as part of removal
    }

    public function testMove(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        // string destination
        $dest = $this->path('moved.txt');
        $result = $file->move($dest);
        $this->assertSame($dest, $result->path());
        $this->assertFileExists($dest);

        // moving into a DirectoryObject: keeps the same file name under that directory
        touch($this->path('another.txt'));
        $another = new FileObject($this->path('another.txt'), 'r');
        mkdir($this->dir . '/sub');
        $directory = new DirectoryObject($this->dir . '/sub');
        $moved = $another->move($directory);
        $this->assertSame($this->dir . '/sub/another.txt', $moved->path());

        // source doesn't exist: null instead of throwing
        touch($this->path('missing-source.txt'));
        $ghost = new FileObject($this->path('missing-source.txt'));
        unlink($this->path('missing-source.txt'));
        $this->assertNull($ghost->move($this->path('nowhere.txt')));
    }

    public function testSetFlagsAndGetFlags(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $file->setFlags(FileObject::FLAG_DROP_NEW_LINE);

        $this->assertSame(FileObject::FLAG_DROP_NEW_LINE, $file->getFlags() & FileObject::FLAG_DROP_NEW_LINE);
    }

    public function testEnable(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $file->enable(FileObject::FLAG_READ_CSV);

        $this->assertNotSame(0, $file->getFlags() & FileObject::FLAG_READ_CSV);
    }

    public function testDisable(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');
        $file->enable(FileObject::FLAG_READ_CSV);

        $file->disable(FileObject::FLAG_READ_CSV);

        $this->assertSame(0, $file->getFlags() & FileObject::FLAG_READ_CSV);
    }

    public function testEnableDropNewLine(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $file->enableDropNewLine();

        $this->assertNotSame(0, $file->getFlags() & FileObject::FLAG_DROP_NEW_LINE);
    }

    public function testEnableReadAhead(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $file->enableReadAhead();

        $this->assertNotSame(0, $file->getFlags() & FileObject::FLAG_READ_AHEAD);
    }

    public function testEnableSkipEmpty(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $file->enableSkipEmpty();

        $this->assertNotSame(0, $file->getFlags() & FileObject::FLAG_SKIP_EMPTY);
    }

    public function testEnableReadCsv(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $file->enableReadCsv();

        $this->assertNotSame(0, $file->getFlags() & FileObject::FLAG_READ_CSV);
    }

    public function testDisableDropNewLine(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');
        $file->enableDropNewLine();

        $file->disableDropNewLine();

        $this->assertSame(0, $file->getFlags() & FileObject::FLAG_DROP_NEW_LINE);
    }

    public function testDisableReadAhead(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');
        $file->enableReadAhead();

        $file->disableReadAhead();

        $this->assertSame(0, $file->getFlags() & FileObject::FLAG_READ_AHEAD);
    }

    public function testDisableSkipEmpty(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');
        $file->enableSkipEmpty();

        $file->disableSkipEmpty();

        $this->assertSame(0, $file->getFlags() & FileObject::FLAG_SKIP_EMPTY);
    }

    public function testDisableReadCsv(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');
        $file->enableReadCsv();

        $file->disableReadCsv();

        $this->assertSame(0, $file->getFlags() & FileObject::FLAG_READ_CSV);
    }

    public function testCsvSettings(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        // no arguments: returns the current control chars without changing them
        $default = $file->csvSettings();
        $this->assertSame([',', '"', '\\'], $default);

        // arguments provided: changes them, then returns the new settings
        $updated = $file->csvSettings(';', "'", '/');
        $this->assertSame([';', "'", '/'], $updated);
    }

    public function testCsvRead(): void
    {
        file_put_contents($this->path(), "a,b\n1,2\n");
        $file = new FileObject($this->path(), 'r');

        $rows = $file->csvRead();

        $this->assertSame(['a', 'b'], $rows[0]);
        $this->assertSame(['1', '2'], $rows[1]);
    }

    public function testCsvReadLine(): void
    {
        file_put_contents($this->path(), "a,b\n");
        $file = new FileObject($this->path(), 'r');

        $this->assertSame(['a', 'b'], $file->csvReadLine());

        // one trailing read still sees a phantom empty line before reaching real EOF
        $file->csvReadLine();

        // past EOF: null instead of false
        $this->assertNull($file->csvReadLine());
    }

    public function testCsvReadCallback(): void
    {
        file_put_contents($this->path(), "a,b\n1,2\n");
        $file = new FileObject($this->path(), 'r');

        $seen = [];
        $file->csvReadCallback(function ($row) use (&$seen) { $seen[] = $row; });

        $this->assertSame(['a', 'b'], $seen[0]);
        $this->assertSame(['1', '2'], $seen[1]);
    }

    public function testWriteCSV(): void
    {
        $file = new FileObject($this->path(), 'w');

        $bytes = $file->writeCSV(['a', 'b']);

        $this->assertGreaterThan(0, $bytes);
        $this->assertSame("a,b\n", file_get_contents($this->path()));
    }

    public function testWriteCSVBulk(): void
    {
        $file = new FileObject($this->path(), 'w');

        $count = $file->writeCSVBulk([['a', 'b'], ['1', '2']]);

        $this->assertGreaterThan(0, $count);
        $this->assertSame("a,b\n1,2\n", file_get_contents($this->path()));
    }

    public function testPath(): void
    {
        $file = new FileObject($this->path());

        $this->assertSame($this->path(), $file->path());
    }

    public function testPathReal(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        $this->assertSame(realpath($this->path()), $file->pathReal());
    }

    public function testPathDirectory(): void
    {
        $file = new FileObject($this->path());

        $this->assertSame($this->dir, $file->pathDirectory());
    }

    public function testExtension(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertSame('txt', $file->extension());
    }

    public function testName(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertSame('file.txt', $file->name());
        $this->assertSame('file', $file->name(false));
    }

    public function testLinkTarget(): void
    {
        touch($this->path());
        $link = $this->path('link.txt');
        symlink($this->path(), $link);
        $file = new FileObject($link, 'r');

        $this->assertSame($this->path(), $file->linkTarget());
    }

    public function testLinkInfo(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        // no property: full lstat() array
        $this->assertIsArray($file->linkInfo());

        // explicit property
        $this->assertIsInt($file->linkInfo('mtime'));
    }

    public function testLinkTimestampAccess(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        $this->assertIsInt($file->linkTimestampAccess());
    }

    public function testLinkTimestampModify(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        $this->assertIsInt($file->linkTimestampModify());
    }

    public function testLinkTimestampChange(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        $this->assertIsInt($file->linkTimestampChange());
    }

    public function testType(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertSame('file', $file->type());
    }

    public function testSize(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path(), 'r');

        $this->assertSame(5, $file->size());
    }

    public function testOwnerID(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertIsInt($file->ownerID());
    }

    public function testGroupID(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertIsInt($file->groupID());
    }

    public function testPermissions(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertIsInt($file->permissions());
    }

    public function testInode(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertIsInt($file->inode());
    }

    public function testTimestampAccess(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertIsInt($file->timestampAccess());
    }

    public function testTimestampChange(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertIsInt($file->timestampChange());
    }

    public function testTimestampModified(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertIsInt($file->timestampModified());
    }

    public function testInfo(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertInstanceOf(\SplFileInfo::class, $file->info());
    }

    public function testDirectoryInfo(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertInstanceOf(\SplFileInfo::class, $file->directoryInfo());
    }

    public function testIsEof(): void
    {
        file_put_contents($this->path(), 'x');
        $file = new FileObject($this->path(), 'r');

        $this->assertFalse($file->isEof());

        $file->read();
        $file->readChar(); // trigger the internal EOF check
        $this->assertTrue($file->isEof());
    }

    public function testIsReadable(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertTrue($file->isReadable());
    }

    public function testIsWritable(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r+');

        $this->assertTrue($file->isWritable());
    }

    public function testIsExecutable(): void
    {
        touch($this->path());
        chmod($this->path(), 0755);
        $file = new FileObject($this->path(), 'r');

        $this->assertTrue($file->isExecutable());
    }

    public function testIsDirectory(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertFalse($file->isDirectory());
    }

    public function testIsFile(): void
    {
        touch($this->path());
        $file = new FileObject($this->path(), 'r');

        $this->assertTrue($file->isFile());
    }

    public function testIsLink(): void
    {
        touch($this->path());
        $link = $this->path('link.txt');
        symlink($this->path(), $link);
        $file = new FileObject($link, 'r');

        $this->assertTrue($file->isLink());
    }

    public function testIsOpened(): void
    {
        $file = new FileObject($this->path());
        $this->assertFalse($file->isOpened());

        touch($this->path());
        $file->open('r');
        $this->assertTrue($file->isOpened());
    }

    public function testIsExist(): void
    {
        $file = new FileObject($this->path());

        $this->assertFalse($file->isExist());

        touch($this->path());
        $this->assertTrue($file->isExist());
    }

    public function testIsModeValid(): void
    {
        $this->assertTrue(FileObject::isModeValid('r'));
        $this->assertTrue(FileObject::isModeValid('a+b'));
        $this->assertFalse(FileObject::isModeValid('not-a-mode'));
        $this->assertFalse(FileObject::isModeValid(null));
    }

    public function testAssertExist(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        // no exception when it exists, and it's chainable
        $this->assertSame($file, $file->assertExist());

        $missing = new FileObject($this->path('missing.txt'));
        $this->expectException(\Exception::class);
        $this->silently(fn () => $missing->assertExist());
    }

    public function testCacheReset(): void
    {
        touch($this->path());
        $file = new FileObject($this->path());

        // no return value to assert beyond the fluent chain
        $this->assertSame($file, $file->cacheReset());
    }

    public function testHashCRC32(): void
    {
        file_put_contents($this->path(), 'hello');
        $file = new FileObject($this->path());

        $this->assertSame(hash_file('crc32b', $this->path()), $file->hashCRC32());
    }
}

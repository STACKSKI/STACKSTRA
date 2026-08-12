<?php

namespace Stackstra\Tests\Filesystem;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Filesystem\Directory;
use Stackstra\Filesystem\DirectoryObject;
use Stackstra\Filesystem\FileObject;
use Stackstra\Filesystem\Search;
use Stackstra\Tests\TestCase;

#[CoversClass(Search::class)]
class SearchTest extends TestCase
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

    public function testConstructAndMake(): void
    {
        // constructor sets all the documented defaults
        $search = new Search($this->dir);

        $this->assertSame($this->dir, $search->path());
        $this->assertSame(Search::DEFAULT_PATTERN, $search->pattern());
        $this->assertSame(Search::DEFAULT_TYPES, $search->types());
        $this->assertSame(Search::DEFAULT_SORT, $search->sort());
        $this->assertSame(Search::DEFAULT_ORDER, $search->order());
        $this->assertFalse($search->recursive());

        // make() behaves identically
        $search = Search::make($this->dir);

        $this->assertSame($this->dir, $search->path());
    }

    public function testPath(): void
    {
        $search = new Search($this->dir);

        // getter (no argument)
        $this->assertSame($this->dir, $search->path());

        // setter: fluent, updates the setting
        $result = $search->path('/other');
        $this->assertSame($search, $result);
        $this->assertSame('/other', $search->path());
    }

    public function testPattern(): void
    {
        $search = new Search($this->dir);

        $this->assertSame('*', $search->pattern());

        $search->pattern('*.txt');
        $this->assertSame('*.txt', $search->pattern());
    }

    public function testTypes(): void
    {
        $search = new Search($this->dir);

        $this->assertSame(Search::TYPE_ANY, $search->types());

        $search->types(Search::TYPE_FILE);
        $this->assertSame(Search::TYPE_FILE, $search->types());
    }

    public function testTypeAny(): void
    {
        $search = (new Search($this->dir))->types(Search::TYPE_FILE)->typeAny();

        $this->assertSame(Search::TYPE_ANY, $search->types());
    }

    public function testTypeFile(): void
    {
        $search = (new Search($this->dir))->typeFile();

        $this->assertSame(Search::TYPE_FILE, $search->types());
    }

    public function testTypeLink(): void
    {
        $search = (new Search($this->dir))->typeLink();

        $this->assertSame(Search::TYPE_LINK, $search->types());
    }

    public function testTypeDirectory(): void
    {
        $search = (new Search($this->dir))->typeDirectory();

        $this->assertSame(Search::TYPE_DIR, $search->types());
    }

    public function testIsType(): void
    {
        $search = (new Search($this->dir))->types(Search::TYPE_FILE | Search::TYPE_LINK);

        $this->assertTrue($search->isType(Search::TYPE_FILE));
        $this->assertFalse($search->isType(Search::TYPE_DIR));
    }

    public function testIsTypeAny(): void
    {
        $search = new Search($this->dir);

        $this->assertTrue($search->isTypeAny());
    }

    public function testIsTypeFile(): void
    {
        $search = (new Search($this->dir))->typeFile();

        $this->assertTrue($search->isTypeFile());
        $this->assertFalse($search->isTypeDirectory());
    }

    public function testIsTypeLink(): void
    {
        $search = (new Search($this->dir))->typeLink();

        $this->assertTrue($search->isTypeLink());
    }

    public function testIsTypeDirectory(): void
    {
        $search = (new Search($this->dir))->typeDirectory();

        $this->assertTrue($search->isTypeDirectory());
    }

    public function testSort(): void
    {
        $search = new Search($this->dir);

        $this->assertSame(Search::SORT_NATURAL, $search->sort());

        $search->sort(Search::SORT_NONE);
        $this->assertSame(Search::SORT_NONE, $search->sort());
    }

    public function testSortNatural(): void
    {
        $search = (new Search($this->dir))->sort(Search::SORT_NONE)->sortNatural();

        $this->assertSame(Search::SORT_NATURAL, $search->sort());
    }

    public function testOrder(): void
    {
        $search = new Search($this->dir);

        $this->assertSame(Search::ORDER_ASC, $search->order());

        $search->order(Search::ORDER_DESC);
        $this->assertSame(Search::ORDER_DESC, $search->order());
    }

    public function testOrderDesc(): void
    {
        $search = (new Search($this->dir))->orderDesc();

        $this->assertSame(Search::ORDER_DESC, $search->order());
    }

    public function testOrderAsc(): void
    {
        $search = (new Search($this->dir))->orderDesc()->orderAsc();

        $this->assertSame(Search::ORDER_ASC, $search->order());
    }

    public function testRecursive(): void
    {
        $search = new Search($this->dir);

        $this->assertFalse($search->recursive());

        $search->recursive(true);
        $this->assertTrue($search->recursive());
    }

    public function testFind(): void
    {
        touch($this->dir . '/a.txt');
        touch($this->dir . '/b.txt');
        mkdir($this->dir . '/sub');

        // default: all types, non-recursive, natural ascending order
        $results = Search::make($this->dir)->find();

        $this->assertCount(3, $results);
        $this->assertContainsOnlyInstancesOf(FileObject::class, array_filter($results, fn ($r) => !$r instanceof DirectoryObject));

        // typeFile() filters out directories
        $results = Search::make($this->dir)->typeFile()->find();

        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(FileObject::class, $results);

        // typeDirectory() filters out files
        $results = Search::make($this->dir)->typeDirectory()->find();

        $this->assertCount(1, $results);
        $this->assertInstanceOf(DirectoryObject::class, $results[0]);

        // pattern filters by glob
        $results = Search::make($this->dir)->typeFile()->pattern('a.*')->find();

        $this->assertCount(1, $results);
        $this->assertSame($this->dir . '/a.txt', $results[0]->path());

        // orderDesc() reverses the natural sort order
        $results = Search::make($this->dir)->typeFile()->orderDesc()->find();

        $this->assertSame($this->dir . '/b.txt', $results[0]->path());
        $this->assertSame($this->dir . '/a.txt', $results[1]->path());

        // sort=SORT_NONE: order is whatever the filesystem glob returned, count still correct
        $results = Search::make($this->dir)->typeFile()->sort(Search::SORT_NONE)->find();

        $this->assertCount(2, $results);

        // recursive=true finds files inside subdirectories too
        touch($this->dir . '/sub/c.txt');
        $results = Search::make($this->dir)->typeFile()->recursive(true)->find();

        $this->assertCount(3, $results);

        // non-existent path: asserts and throws rather than silently returning []
        $this->expectException(\Exception::class);
        $this->silently(fn () => Search::make($this->dir . '/missing')->find());
    }
}

<?php

namespace Stackstra\Tests\Filesystem\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Filesystem\Search;
use Stackstra\Filesystem\Traits\CanSearch;
use Stackstra\Tests\TestCase;

#[CoversClass(CanSearch::class)]
class CanSearchTest extends TestCase
{
    public function testSearch(): void
    {
        $path = sys_get_temp_dir();

        $subject = new class ($path) {
            use CanSearch;

            public function __construct(private string $p) {}
            public function path(): string { return $this->p; }
        };

        $search = $subject->search();

        $this->assertInstanceOf(Search::class, $search);
        $this->assertSame($path, $search->path());
    }
}

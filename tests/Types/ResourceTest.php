<?php

namespace Stackstra\Tests\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Types\Resource;

#[CoversClass(Resource::class)]
class ResourceTest extends TestCase
{
    public function testInfo(): void
    {
        $resource = fopen('php://temp', 'r+');

        $this->assertSame('php://temp', Resource::info($resource, 'uri'));
        $this->assertArrayHasKey('mode', Resource::info($resource));

        fclose($resource);
    }

    public function testPath(): void
    {
        $resource = fopen('php://temp', 'r+');

        $this->assertSame('php://temp', Resource::path($resource));

        fclose($resource);
    }
}

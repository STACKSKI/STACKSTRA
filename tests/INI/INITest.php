<?php

namespace Stackstra\Tests\INI;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\INI\INI;
use Stackstra\Tests\TestCase;

#[CoversClass(INI::class)]
class INITest extends TestCase
{
    private const CONTENT = "[section]\n; a comment\nkey = value\nempty_ignored_line\nsecond = another value\n";

    public function testParseFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ini_');
        file_put_contents($path, self::CONTENT);

        $ini = new INI();
        $ini->parseFile($path);

        $this->assertSame('value', $ini->get('key'));
        $this->assertSame('another value', $ini->get('second'));

        unlink($path);
    }

    public function testParseString(): void
    {
        $ini = new INI();
        $ini->parseString(self::CONTENT);

        // section headers, comment lines, and lines without "=" are skipped
        $this->assertSame(['key' => 'value', 'second' => 'another value'], $ini->get());

        // both sides of "=" are trimmed
        $ini->parseString("  padded  =  value  \n");
        $this->assertSame('value', $ini->get('padded'));

        // re-parsing replaces the previous settings entirely
        $ini->parseString("only = this\n");
        $this->assertSame(['only' => 'this'], $ini->get());
    }

    public function testGet(): void
    {
        $ini = new INI();
        $ini->parseString("a = 1\nb = 2\n");

        // no index: the whole settings array
        $this->assertSame(['a' => '1', 'b' => '2'], $ini->get());

        // existing index
        $this->assertSame('1', $ini->get('a'));

        // missing index: null
        $this->assertNull($ini->get('missing'));
    }
}

<?php

namespace Stackstra\Tests\CSS;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\CSS\Compressor;
use Stackstra\Tests\TestCase;

#[CoversClass(Compressor::class)]
class CompressorTest extends TestCase
{
    public function testCompress(): void
    {
        // comments are stripped entirely
        $this->assertSame('a{color:red;}', Compressor::compress("/* comment */a { color: red; }\n"));

        // multi-line comments spanning several lines are also stripped
        $this->assertSame('a{color:red;}', Compressor::compress("/*\n multi\n line\n */a { color: red; }"));

        // newlines, carriage returns, tabs and double spaces are removed
        $this->assertSame('a{color:red;}b{color:blue;}', Compressor::compress("a { color: red; }\r\nb {\tcolor: blue; }"));

        // space around braces/semicolons/commas is collapsed on both sides; colon space is only collapsed after the colon
        $this->assertSame('a,b{color :red;margin :0;}', Compressor::compress('a , b { color : red ; margin : 0 ; }'));

        // input with nothing to compress is returned unchanged
        $this->assertSame('a{color:red;}', Compressor::compress('a{color:red;}'));
    }
}

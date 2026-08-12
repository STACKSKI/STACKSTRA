<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\MIME;

#[CoversClass(MIME::class)]
class MIMETest extends TestCase
{
    public function testIsImage(): void
    {
        $this->assertTrue(MIME::isImage('image/png'));

        $this->assertFalse(MIME::isImage('audio/mpeg'));
        $this->assertFalse(MIME::isImage('text/html'));
    }

    public function testIsAudio(): void
    {
        $this->assertTrue(MIME::isAudio('audio/mpeg'));

        $this->assertFalse(MIME::isAudio('image/png'));
    }
}

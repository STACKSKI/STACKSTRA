<?php

namespace Stackstra\Tests\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Console\Console;
use Stackstra\Tests\TestCase;

#[CoversClass(Console::class)]
class ConsoleTest extends TestCase
{
    public function testIsExist(): void
    {
        // running under the CLI SAPI in these tests
        $this->assertSame(APP_CLI, Console::isExist());
    }

    public function testWrite(): void
    {
        // default: newline-terminated
        ob_start();
        $result = Console::write('hello');
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame('hello' . PHP_EOL, $output);

        // replace=true: carriage return instead of newline
        ob_start();
        Console::write('hello', replace: true);
        $output = ob_get_clean();

        $this->assertSame("hello\r", $output);

        // bold=true: wrapped in ANSI bold codes
        ob_start();
        Console::write('hello', bold: true);
        $output = ob_get_clean();

        $this->assertSame(Console::bold('hello') . PHP_EOL, $output);

        // custom newline argument
        ob_start();
        Console::write('hello', newline: '---');
        $output = ob_get_clean();

        $this->assertSame('hello---', $output);
    }

    public function testWriteTimestamp(): void
    {
        ob_start();
        Console::writeTimestamp('hello', timestamp_format: 'Y');
        $output = ob_get_clean();

        $this->assertSame('[' . date('Y') . '] hello' . PHP_EOL, $output);

        // var omitted (empty string): timestamp only, no trailing " message"
        ob_start();
        Console::writeTimestamp(timestamp_format: 'Y');
        $output = ob_get_clean();

        $this->assertSame('[' . date('Y') . ']' . PHP_EOL, $output);
    }

    public function testBold(): void
    {
        $this->assertSame(Console::colorize('hello', true), Console::bold('hello'));
    }

    public function testColorize(): void
    {
        // no flags: just the string, wrapped in an empty escape sequence
        $this->assertSame("\033[m" . 'hello' . "\033[0m", Console::colorize('hello'));

        // bold only
        $this->assertSame("\033[1m" . 'hello' . "\033[0m", Console::colorize('hello', true));

        // foreground color
        $this->assertSame("\033[31m" . 'hello' . "\033[0m", Console::colorize('hello', false, 'red'));

        // bold + foreground + background, semicolon-joined in order
        $this->assertSame("\033[1;31;41m" . 'hello' . "\033[0m", Console::colorize('hello', true, 'red', 'red'));
    }

    public function testLines(): void
    {
        ob_start();
        Console::lines();
        $output = ob_get_clean();

        $this->assertSame(str_repeat('-', Console::cols()) . PHP_EOL, $output);

        // custom character
        ob_start();
        Console::lines('=');
        $output = ob_get_clean();

        $this->assertSame(str_repeat('=', Console::cols()) . PHP_EOL, $output);
    }

    public function testCols(): void
    {
        // TODO in the source: always returns the default, regardless of $refresh
        $this->assertSame(80, Console::cols());
        $this->assertSame(120, Console::cols(120));
    }

    public function testRows(): void
    {
        $this->assertSame(24, Console::rows());
        $this->assertSame(50, Console::rows(50));
    }

    public function testIsExistCommand(): void
    {
        $this->assertTrue(Console::isExistCommand('php'));
        $this->assertFalse(Console::isExistCommand('definitely-not-a-real-command-xyz'));
    }

    public function testClear(): void
    {
        ob_start();
        Console::clear();
        $output = ob_get_clean();

        $this->assertSame("\033[H\033[2J", $output);
    }

    public function testProgress(): void
    {
        // current > total: no output at all
        ob_start();
        Console::progress(5, 2);
        $this->assertSame('', ob_get_clean());

        // mid-progress: no trailing newline
        ob_start();
        Console::progress(5, 10, cells: 10);
        $output = ob_get_clean();

        $this->assertStringContainsString('(05/10)', $output);
        $this->assertStringContainsString('50.00%', $output);
        $this->assertStringNotContainsString(PHP_EOL, rtrim($output, PHP_EOL));

        // complete (current == total): trailing newline appended, unless disabled
        ob_start();
        Console::progress(10, 10, cells: 10);
        $output = ob_get_clean();

        $this->assertStringEndsWith(PHP_EOL, $output);

        ob_start();
        Console::progress(10, 10, cells: 10, newline_on_complete: false);
        $output = ob_get_clean();

        $this->assertStringEndsNotWith(PHP_EOL, $output);

        // labels and postfix are appended with a leading space
        ob_start();
        Console::progress(1, 10, label_current: 'done', label_total: 'total', postfix: 'left');
        $output = ob_get_clean();

        $this->assertStringContainsString('done', $output);
        $this->assertStringContainsString('total', $output);
        $this->assertStringContainsString('left', $output);
    }
}

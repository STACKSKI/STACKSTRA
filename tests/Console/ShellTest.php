<?php

namespace Stackstra\Tests\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Console\Shell;
use Stackstra\Tests\TestCase;

#[CoversClass(Shell::class)]
class ShellTest extends TestCase
{
    public function testConstruct(): void
    {
        $shell = new Shell('echo hi');

        $this->assertSame('echo hi', $shell->query());

        // command omitted: defaults to an empty string
        $shell = new Shell();

        $this->assertSame('', $shell->query());
    }

    public function testRun(): void
    {
        $shell = Shell::run('echo', ['-n' => 'hello']);

        $this->assertSame(0, $shell->code());
        $this->assertSame('hello', $shell->output());
    }

    public function testCommand(): void
    {
        $shell = (new Shell())->command('echo test');

        $this->assertSame('echo test', $shell->query());
    }

    public function testOptions(): void
    {
        $shell = (new Shell('cmd'))->options(['-a' => '1', '-b' => null]);

        // numeric-looking argument values are left unescaped by escape()
        $this->assertSame('cmd -a 1 -b', $shell->query());

        // subsequent call adds to (not replaces) the existing options
        $shell->options(['-c' => '2']);
        $this->assertSame('cmd -a 1 -b -c 2', $shell->query());
    }

    public function testOption(): void
    {
        $shell = (new Shell('cmd'))->option('-x', 'value');

        $this->assertSame("cmd -x 'value'", $shell->query());

        // argument omitted: flag with no value
        $shell = (new Shell('cmd'))->option('-y');
        $this->assertSame('cmd -y', $shell->query());
    }

    public function testArgument(): void
    {
        $shell = (new Shell('cmd'))->argument('file.txt')->argument('other file.txt');

        $this->assertSame("cmd 'file.txt' 'other file.txt'", $shell->query());
    }

    public function testQuery(): void
    {
        $shell = (new Shell('cmd'))->option('-a', '1')->argument('arg');

        $this->assertSame("cmd -a 1 'arg'", $shell->query());
    }

    public function testEscape(): void
    {
        // numeric values pass through unescaped
        $this->assertSame('5', Shell::escape(5));
        $this->assertSame('5.5', Shell::escape(5.5));

        // non-numeric values are shell-escaped
        $this->assertSame(escapeshellarg('hello world'), Shell::escape('hello world'));
    }

    public function testExec(): void
    {
        $shell = (new Shell('echo'))->option('-n', 'output');

        $result = $shell->exec();

        $this->assertSame($shell, $result);
        $this->assertSame(0, $shell->code());
        $this->assertSame('output', $shell->output());
    }

    public function testOutput(): void
    {
        $shell = Shell::run('printf', ['%s\\\\n%s' => null]); // two lines via printf format

        // to_string=true (default): joined with PHP_EOL
        $this->assertIsString($shell->output());

        // to_string=false: raw array of lines
        $this->assertIsArray($shell->output(false));
    }

    public function testCode(): void
    {
        $shell = Shell::run('true');
        $this->assertSame(0, $shell->code());

        $shell = Shell::run('false');
        $this->assertSame(1, $shell->code());

        // never executed: null
        $fresh = new Shell('echo hi');
        $this->assertNull($fresh->code());
    }

    public function testReset(): void
    {
        $shell = Shell::run('echo', ['-n' => 'x']);

        $shell->reset();

        $this->assertSame('', $shell->query());
        $this->assertSame('', $shell->output());
        $this->assertNull($shell->code());
    }
}

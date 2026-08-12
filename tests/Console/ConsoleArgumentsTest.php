<?php

namespace Stackstra\Tests\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Console\ConsoleArguments;
use Stackstra\Tests\TestCase;

/**
 * ConsoleArguments::get()/getRaw() cache their result in a function-local static, so each
 * scenario (different simulated $argv) needs its own fresh PHP process.
 */
#[CoversClass(ConsoleArguments::class)]
class ConsoleArgumentsTest extends TestCase
{
    /**
     * @param string[] $argv arguments to pass after the script name (script name itself is
     *                       always $argv[0] and is skipped internally by ConsoleArguments::get())
     */
    private function runPhp(array $argv, string $code): string
    {
        $bootstrap = 'require ' . var_export(__DIR__ . '/../../vendor/autoload.php', true) . ';'
                   . 'use Stackstra\Console\ConsoleArguments;';

        $command = 'php -d register_argc_argv=1 -r ' . escapeshellarg($bootstrap . $code) . ' --';

        foreach ($argv as $arg)
        {
            $command .= ' ' . escapeshellarg($arg);
        }

        return trim(shell_exec($command));
    }

    public function testCount(): void
    {
        $output = $this->runPhp(['a', 'b'], 'echo ConsoleArguments::count();');

        // the script name itself counts as an argument too
        $this->assertSame('3', $output);
    }

    public function testGet(): void
    {
        // "-name value" pairs are captured as key => value; bare flags map to themselves
        $output = $this->runPhp(['-name', 'Alice', '--verbose'], 'var_export(ConsoleArguments::get());');

        $this->assertStringContainsString("'name' => 'Alice'", $output);
        $this->assertStringContainsString("'verbose' => 'verbose'", $output);

        // explicit name argument: single value
        $output = $this->runPhp(['-name', 'Alice'], 'echo ConsoleArguments::get("name");');

        $this->assertSame('Alice', $output);

        // no CLI arguments at all: empty array
        $output = $this->runPhp([], 'var_export(ConsoleArguments::get());');

        $this->assertSame('array (' . "\n" . ')', $output);

        // a flag immediately followed by another flag: treated as two independent bare flags
        $output = $this->runPhp(['-a', '-b'], 'var_export(ConsoleArguments::get());');

        $this->assertStringContainsString("'a' => 'a'", $output);
        $this->assertStringContainsString("'b' => 'b'", $output);
    }

    public function testGetRaw(): void
    {
        $output = $this->runPhp(['x', 'y'], 'var_export(ConsoleArguments::getRaw());');

        $this->assertStringContainsString("'x'", $output);
        $this->assertStringContainsString("'y'", $output);
    }

    public function testIsExist(): void
    {
        $code = '$r = [
            ConsoleArguments::isExist("name"),
            ConsoleArguments::isExist("missing"),
            ConsoleArguments::isExist("name", "Alice"),
            ConsoleArguments::isExist("name", "Bob"),
            ConsoleArguments::isExist("name", "Alice", true),
            ConsoleArguments::isExist("name", 0, true),
        ];
        echo implode(",", array_map("intval", $r));';

        $output = $this->runPhp(['-name', 'Alice'], $code);

        // exist / missing / loose-match / loose-mismatch / strict-match / strict-mismatch
        $this->assertSame('1,0,1,0,1,0', $output);
    }
}

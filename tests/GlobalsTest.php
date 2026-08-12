<?php

namespace Stackstra\Tests;

use ArrayObject;
use PHPUnit\Framework\Attributes\CoversFunction;
use ReflectionClass;
use stdClass;
use Stackstra\Cache\Cache;
use Stackstra\Console\Shell;
use Stackstra\Etc\Debug;
use Stackstra\Etc\Timer;

use function Stackstra\debug;
use function Stackstra\dd;
use function Stackstra\delete;
use function Stackstra\false_to_null;
use function Stackstra\format;
use function Stackstra\get;
use function Stackstra\is_nullptr;
use function Stackstra\is_set;
use function Stackstra\pull;
use function Stackstra\set;
use function Stackstra\shell;
use function Stackstra\timer;
use function Stackstra\value;
use function Stackstra\cacher;

#[CoversFunction('Stackstra\is_nullptr')]
#[CoversFunction('Stackstra\get')]
#[CoversFunction('Stackstra\set')]
#[CoversFunction('Stackstra\is_set')]
#[CoversFunction('Stackstra\delete')]
#[CoversFunction('Stackstra\pull')]
#[CoversFunction('Stackstra\debug')]
#[CoversFunction('Stackstra\dd')]
#[CoversFunction('Stackstra\shell')]
#[CoversFunction('Stackstra\timer')]
#[CoversFunction('Stackstra\format')]
#[CoversFunction('Stackstra\false_to_null')]
#[CoversFunction('Stackstra\value')]
#[CoversFunction('Stackstra\cacher')]
class GlobalsTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // reset Debug's cached static state so tests don't leak into each other
        $property = (new ReflectionClass(Debug::class))->getProperty('is_enabled');
        $property->setValue(null, null);
    }

    public function testIsNullptr(): void
    {
        $this->assertTrue(is_nullptr(NULLPTR));

        $this->assertFalse(is_nullptr(5));
        $this->assertFalse(is_nullptr(null));
    }

    public function testGet(): void
    {
        // single argument: $what is read from $_REQUEST
        $_REQUEST['foo'] = 'bar';
        $this->assertSame('bar', get('foo'));
        unset($_REQUEST['foo']);

        // array $src, scalar $what: existing/missing/missing-with-default
        $array = ['a' => 1, 'b' => 2];
        $this->assertSame(1, get($array, 'a'));
        $this->assertNull(get($array, 'missing'));
        $this->assertSame('fallback', get($array, 'missing', 'fallback'));

        // object $src with a public property
        $object = new stdClass();
        $object->x = 42;
        $this->assertSame(42, get($object, 'x'));
        $this->assertSame('default', get($object, 'missing', 'default'));

        // object $src implementing ArrayAccess
        $array_access = new ArrayObject(['k' => 'v']);
        $this->assertSame('v', get($array_access, 'k'));

        // array $what, $default is array/object: $what maps a $default key to an output key
        $result = get('ignored', ['a' => 'A', 'b' => 'B'], ['a' => 1, 'b' => 2]);
        $this->assertSame(['A' => 1, 'B' => 2], $result);

        // array $what, $default is a scalar: every output key gets that same scalar default
        $result = get('ignored', ['a' => 'A', 'b' => 'B'], 'fallback');
        $this->assertSame(['A' => 'fallback', 'B' => 'fallback'], $result);
    }

    public function testSet(): void
    {
        // array $items (passed by reference)
        $array = ['a' => 1];
        set($array, 'b', 2);
        $this->assertSame(['a' => 1, 'b' => 2], $array);

        // object $items
        $object = new stdClass();
        set($object, 'x', 1);
        $this->assertSame(1, $object->x);

        // value omitted: defaults to null
        set($array, 'c');
        $this->assertNull($array['c']);
    }

    public function testIsSet(): void
    {
        $this->assertTrue(is_set(['a' => 1], 'a'));
        $this->assertFalse(is_set(['a' => 1], 'z'));

        $object = new stdClass();
        $object->x = 1;
        $this->assertTrue(is_set($object, 'x'));
        $this->assertFalse(is_set($object, 'z'));
    }

    public function testDelete(): void
    {
        // object: the property is removed
        $object = new stdClass();
        $object->x = 1;
        delete($object, 'x');
        $this->assertFalse(isset($object->x));

        // array: taken by reference, so the key is actually removed from the caller's array
        $array = ['a' => 1, 'b' => 2];
        delete($array, 'a');
        $this->assertSame(['b' => 2], $array);
    }

    public function testPull(): void
    {
        // array $haystack: taken by reference, so the key is actually removed from the caller's array
        $array = ['a' => 1, 'b' => 2];
        $value = pull($array, 'a');
        $this->assertSame(1, $value);
        $this->assertSame(['b' => 2], $array);

        // object $haystack
        $object = new stdClass();
        $object->x = 5;
        $value = pull($object, 'x');
        $this->assertSame(5, $value);
        $this->assertFalse(isset($object->x));

        // missing key: the default is returned, nothing to remove
        $array2 = [];
        $this->assertSame('fallback', pull($array2, 'missing', 'fallback'));
    }

    public function testDebug(): void
    {
        // disabled (the default state): no output at all
        Debug::disable();
        ob_start();
        debug('value');
        $this->assertSame('', ob_get_clean());

        Debug::enable();

        // default: print_r() output, no title
        ob_start();
        debug('value');
        $this->assertSame(print_r('value', true) . PHP_EOL, ob_get_clean());

        // $var_dump=true: var_dump() output instead of print_r()
        ob_start();
        debug('value', true);
        $dumped = ob_get_clean();
        $this->assertStringContainsString('string(5) "value"', $dumped);

        // $title given: prefixed with "[title]: "
        ob_start();
        debug('value', false, 'my title');
        $this->assertSame('[my title]: ' . print_r('value', true) . PHP_EOL, ob_get_clean());
    }

    /**
     * dd() var_dump()s its arguments and then exit()s — untestable in-process. Shell out and confirm
     * both the dump and the clean exit happened, for a single value and for multiple values.
     */
    public function testDd(): void
    {
        $autoload = __DIR__ . '/../vendor/autoload.php';

        $code = 'require ' . var_export($autoload, true) . ';'
              . '\Stackstra\dd("first", 42);'
              . 'fwrite(STDOUT, "UNREACHABLE");';

        $output = shell_exec('php -r ' . escapeshellarg($code) . '; echo "|exit:$?"');

        $this->assertStringContainsString('string(5) "first"', $output);
        $this->assertStringContainsString('int(42)', $output);
        $this->assertStringNotContainsString('UNREACHABLE', $output);
        $this->assertStringEndsWith('|exit:1', trim($output));
    }

    public function testShell(): void
    {
        $result = shell('echo', ['shell-test-output' => null]);

        $this->assertInstanceOf(Shell::class, $result);
        $this->assertSame('shell-test-output', $result->output());
        $this->assertSame(0, $result->code());
    }

    public function testTimer(): void
    {
        $timer = timer();

        $this->assertInstanceOf(Timer::class, $timer);
        $this->assertGreaterThanOrEqual(0, $timer->diff());

        // singleton: repeated calls return the same instance
        $this->assertSame($timer, timer());
    }

    public function testFormat(): void
    {
        $this->assertSame('x-5', format('%s-%d', 'x', 5));
    }

    public function testFalseToNull(): void
    {
        // only `false` is converted; other falsy values pass through unchanged
        $this->assertNull(false_to_null(false));
        $this->assertSame(0, false_to_null(0));
        $this->assertSame('', false_to_null(''));
        $this->assertSame('value', false_to_null('value'));
    }

    public function testValue(): void
    {
        // Closure: invoked and the return value is used
        $this->assertSame(10, value(fn () => 10));

        // any other value: returned as-is
        $this->assertSame(5, value(5));
    }

    public function testCacher(): void
    {
        // same index: the same Cache instance is returned
        $index = $this->faker->uuid();
        $cache_a = cacher($index);
        $cache_b = cacher($index);
        $this->assertInstanceOf(Cache::class, $cache_a);
        $this->assertSame($cache_a, $cache_b);

        // a different index: a distinct instance
        $other_index = $this->faker->uuid();
        $cache_c = cacher($other_index);
        $this->assertNotSame($cache_a, $cache_c);
    }
}

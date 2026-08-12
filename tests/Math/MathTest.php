<?php

namespace Stackstra\Tests\Math;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Math\Math;
use Stackstra\Tests\TestCase;

#[CoversClass(Math::class)]
class MathTest extends TestCase
{
    public function testFactorial(): void
    {
        // 0! is defined as 1, and the falsy $number short-circuits the recursion
        $this->assertSame(1, Math::factorial(0));
        $this->assertSame(1, Math::factorial(1));
        $this->assertSame(2, Math::factorial(2));
        $this->assertSame(6, Math::factorial(3));
        $this->assertSame(24, Math::factorial(4));
        $this->assertSame(120, Math::factorial(5));
    }

    public function testFibonacci(): void
    {
        // classic Fibonacci sequence, 1-indexed with fibonacci(1) = 0
        $expected = [0, 1, 1, 2, 3, 5, 8, 13, 21, 34];

        foreach ($expected as $n => $value)
        {
            $this->assertSame($value, (int) Math::fibonacci($n + 1));
        }
    }

    public function testIsPrime(): void
    {
        // 1 is explicitly excluded, even though naive trial division wouldn't reject it
        $this->assertFalse(Math::isPrime(1));

        // 2 is the only even prime
        $this->assertTrue(Math::isPrime(2));

        // other even numbers are never prime
        $this->assertFalse(Math::isPrime(4));

        // odd primes and odd composites
        $this->assertTrue(Math::isPrime(3));
        $this->assertTrue(Math::isPrime(17));
        $this->assertFalse(Math::isPrime(9));
        $this->assertFalse(Math::isPrime(25));
    }

    public function testPrimes(): void
    {
        // limit small enough that the sieve loop never runs: no primes are ever appended
        $this->assertNull(Math::primes(2));

        // limit=3: only index 2 remains marked prime
        $this->assertSame([2], Math::primes(3));

        // limit=10: classic small primes below 10
        $this->assertSame([2, 3, 5, 7], Math::primes(10));

        // default limit=1000: spot-check known primes and a known composite
        $primes = Math::primes();
        $this->assertContains(2, $primes);
        $this->assertContains(997, $primes); // largest prime below 1000
        $this->assertNotContains(1, $primes);
        $this->assertNotContains(999, $primes); // 999 = 3 * 333
    }

    public function testExponentialMovingAverage(): void
    {
        // n omitted: defaults to the full length of $numbers, alpha = 2/(n+1)
        $ema = Math::exponentialMovingAverage([1, 2, 3]);
        $this->assertCount(3, $ema);
        $this->assertEqualsWithDelta([1, 1.5, 2.25], $ema, 0.0001);

        // explicit n changes alpha and thus every subsequent term
        $ema = Math::exponentialMovingAverage([1, 2, 3], 2);
        $this->assertEqualsWithDelta([1, 1.6667, 2.5556], $ema, 0.001);

        // non-sequential keys are reindexed via array_values() before processing
        $ema = Math::exponentialMovingAverage([5 => 1, 9 => 2, 2 => 3]);
        $this->assertEqualsWithDelta([1, 1.5, 2.25], $ema, 0.0001);
    }
}

<?php

namespace Stackstra\Tests\Math;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Math\Crypt;
use Stackstra\Tests\TestCase;

#[CoversClass(Crypt::class)]
class CryptTest extends TestCase
{
    public function testAlgorithms(): void
    {
        $algorithms = Crypt::algorithms();

        $this->assertIsArray($algorithms);
        $this->assertContains('sha256', $algorithms);
        $this->assertSame(hash_algos(), $algorithms);
    }

    public function testRandBytes(): void
    {
        // default length
        $this->assertSame(16, strlen(Crypt::randBytes()));

        // explicit length
        $this->assertSame(32, strlen(Crypt::randBytes(32)));

        // two calls produce different output (extremely unlikely to collide)
        $this->assertNotSame(Crypt::randBytes(), Crypt::randBytes());
    }

    public function testRandNumber(): void
    {
        $min = 5;
        $max = 10;

        for ($i = 0; $i < 20; $i++)
        {
            $number = Crypt::randNumber($min, $max);

            $this->assertGreaterThanOrEqual($min, $number);
            $this->assertLessThanOrEqual($max, $number);
        }

        // min == max: only one possible value
        $this->assertSame(5, Crypt::randNumber(5, 5));
    }

    public function testCrypt(): void
    {
        // salt omitted: one is generated, and appended after the hash
        $result = Crypt::crypt('secret');
        [$hash, $salt] = explode(':', $result, 2);
        $this->assertSame(hash('sha512', 'secret' . $salt), $hash);

        // explicit salt: deterministic hash
        $result = Crypt::crypt('secret', 'fixed-salt');
        $this->assertSame(hash('sha512', 'secretfixed-salt') . ':fixed-salt', $result);

        // explicit algorithm
        $result = Crypt::crypt('secret', 'fixed-salt', algorithm: 'md5');
        $this->assertSame(hash('md5', 'secretfixed-salt') . ':fixed-salt', $result);
    }

    public function testHash(): void
    {
        // default algorithm
        $this->assertSame(hash('sha512', 'passwordsalt'), Crypt::hash('password', 'salt'));

        // explicit algorithm
        $this->assertSame(hash('md5', 'passwordsalt'), Crypt::hash('password', 'salt', 'md5'));
    }

    public function testAutologin(): void
    {
        // falsy user id: empty hash, salt passed through untouched
        $this->assertSame(['', 'salt'], Crypt::autologin(0, 'salt'));
        $this->assertSame(['', null], Crypt::autologin(0));

        // truthy user id, explicit salt: deterministic
        [$hash, $salt] = Crypt::autologin(42, 'fixed-salt');
        $this->assertSame('fixed-salt', $salt);
        $this->assertSame(hash('sha512', '42fixed-salt'), $hash);

        // truthy user id, salt omitted: one is generated
        [$hash, $salt] = Crypt::autologin(42);
        $this->assertNotNull($salt);
        $this->assertSame(hash('sha512', '42' . $salt), $hash);

        // explicit algorithm
        [$hash, $salt] = Crypt::autologin(42, 'fixed-salt', 'md5');
        $this->assertSame(hash('md5', '42fixed-salt'), $hash);
    }

    public function testIsAlgorithmExist(): void
    {
        $this->assertTrue(Crypt::isAlgorithmExist('sha256'));
        $this->assertFalse(Crypt::isAlgorithmExist('not-a-real-algorithm'));
    }

    public function testIsValidCrypt(): void
    {
        $crypt = Crypt::crypt('secret', 'fixed-salt');

        $this->assertTrue(Crypt::isValidCrypt('secret', $crypt));
        $this->assertFalse(Crypt::isValidCrypt('wrong', $crypt));
    }

    public function testIsValidHash(): void
    {
        $this->assertTrue(Crypt::isValidHash('abc', 'abc'));
        $this->assertFalse(Crypt::isValidHash('abc', 'xyz'));
    }

    public function testIsValidPassword(): void
    {
        $hash = Crypt::hash('secret', 'fixed-salt');

        $this->assertTrue(Crypt::isValidPassword('secret', $hash, 'fixed-salt'));
        $this->assertFalse(Crypt::isValidPassword('wrong', $hash, 'fixed-salt'));
    }

    public function testIsValidAutologin(): void
    {
        [$hash, $salt] = Crypt::autologin(42, 'fixed-salt');

        $this->assertTrue(Crypt::isValidAutologin(42, $hash, $salt));
        $this->assertFalse(Crypt::isValidAutologin(42, 'wrong-token', $salt));

        // falsy user id always fails, regardless of the token
        $this->assertFalse(Crypt::isValidAutologin(0, $hash, $salt));
    }

    public function testExplode(): void
    {
        // default limit: unlimited parts
        $this->assertSame(['a', 'b', 'c'], Crypt::explode('a:b:c'));

        // explicit limit caps the number of parts, the remainder stays in the last one
        $this->assertSame(['a', 'b:c'], Crypt::explode('a:b:c', 2));
    }

    public function testCrc(): void
    {
        $this->assertSame(crc32('hello'), Crypt::crc('hello'));
    }

    public function testCrc16(): void
    {
        // known CRC-16/MODBUS test vector for the ASCII string "123456789"
        $this->assertSame(0x4B37, Crypt::crc16('123456789'));

        // empty string: initial register value, untouched
        $this->assertSame(0xFFFF, Crypt::crc16(''));
    }

    public function testCrc32(): void
    {
        $this->assertSame(crc32('hello'), Crypt::crc32('hello'));
    }

    public function testCrc64(): void
    {
        // known test vector from the class docblock
        $this->assertSame('afe4e823e7cef190', Crypt::crc64('php'));

        // format argument controls the output representation
        $this->assertSame('0xafe4e823e7cef190', Crypt::crc64('php', '0x%x'));
        $this->assertSame('0xAFE4E823E7CEF190', Crypt::crc64('php', '0x%X'));
    }
}

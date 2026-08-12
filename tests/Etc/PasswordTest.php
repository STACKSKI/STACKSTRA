<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Tests\TestCase;
use Stackstra\Etc\Password;

#[CoversClass(Password::class)]
class PasswordTest extends TestCase
{
    public function testHash(): void
    {
        $hash = Password::hash('secret');

        $this->assertIsString($hash);
        $this->assertTrue(password_verify('secret', $hash));
    }

    public function testIsValid(): void
    {
        $hash = Password::hash('secret');

        $this->assertTrue(Password::isValid('secret', $hash));

        $this->assertFalse(Password::isValid('wrong', $hash));
    }
}

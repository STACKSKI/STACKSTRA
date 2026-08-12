<?php

namespace Stackstra\Tests\Etc;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Etc\Session;
use Stackstra\Tests\TestCase;

#[CoversClass(Session::class)]
class SessionTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SESSION);
    }

    public function testStart(): void
    {
        // under the CLI SAPI (APP_CLI true), start() always short-circuits to false
        $this->assertFalse(Session::start());
    }

    public function testClose(): void
    {
        // no session active in CLI: writing/closing it must simply not throw
        Session::close();

        $this->assertTrue(true);
    }

    public function testID(): void
    {
        $this->assertSame(session_id(), Session::id());
    }

    public function testGet(): void
    {
        $_SESSION = ['a' => 1];

        // no key: the whole session array
        $this->assertSame(['a' => 1], Session::get());

        // key present
        $this->assertSame(1, Session::get('a'));

        // key missing, default omitted -> null
        $this->assertNull(Session::get('z'));

        // key missing, default provided
        $this->assertSame('fallback', Session::get('z', 'fallback'));
    }

    public function testSet(): void
    {
        $_SESSION = [];

        $this->assertTrue(Session::set('a', 1));
        $this->assertSame(1, $_SESSION['a']);
    }

    public function testRemove(): void
    {
        $_SESSION = ['a' => 1];

        Session::remove('a');
        $this->assertArrayNotHasKey('a', $_SESSION);

        // removing a missing key is a no-op
        Session::remove('z');
        $this->assertArrayNotHasKey('z', $_SESSION);
    }

    public function testIsExist(): void
    {
        // $_SESSION entirely unset: false regardless of key
        unset($_SESSION);
        $this->assertFalse(Session::isExist('a'));

        $_SESSION = ['a' => 1];
        $this->assertTrue(Session::isExist('a'));
        $this->assertFalse(Session::isExist('z'));
    }

    public function testIsEqual(): void
    {
        $_SESSION = ['a' => '1'];

        // key missing: always false
        $this->assertFalse(Session::isEqual('z', '1'));

        // strict (default): type-sensitive
        $this->assertTrue(Session::isEqual('a', '1'));
        $this->assertFalse(Session::isEqual('a', 1));

        // strict=false: loose comparison
        $this->assertTrue(Session::isEqual('a', 1, false));
    }

    public function testStatus(): void
    {
        $this->assertSame(session_status(), Session::status());
    }

    public function testIsActive(): void
    {
        // no session started under the CLI SAPI in these tests
        $this->assertSame(session_status() === PHP_SESSION_ACTIVE, Session::isActive());
    }

    public function testDestroy(): void
    {
        // no active session: false, no side effects
        $this->assertFalse(Session::destroy());
    }

    public function testErase(): void
    {
        // no active session: false, no side effects
        $this->assertFalse(Session::erase());
    }
}

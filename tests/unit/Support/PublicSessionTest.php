<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\PublicSession;
use CodeIgniter\Session\SessionInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/** @internal */
final class PublicSessionTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_COOKIE = [];
        Services::reset(true);
    }

    protected function tearDown(): void
    {
        $_COOKIE = [];
        Services::reset(true);
        parent::tearDown();
    }

    public function testAnonymousRequestDoesNotStartSession(): void
    {
        $this->assertNull(PublicSession::current());
    }

    public function testExistingSessionCookieEnablesSessionAccess(): void
    {
        $_COOKIE[config('Session')->cookieName] = 'existing-session';

        $this->assertInstanceOf(SessionInterface::class, PublicSession::current());
    }
}

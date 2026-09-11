<?php

declare(strict_types=1);

namespace Tests\Unit\Filters;

use App\Filters\CsrfCookieFilter;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/** @internal */
final class CsrfCookieFilterTest extends CIUnitTestCase
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

    public function testEmitsReadableMirrorWithoutWeakeningNativeCookie(): void
    {
        $security = config('Security');
        $request = service('request');
        $response = service('response');

        (new CsrfCookieFilter())->after($request, $response);

        $native = $response->getCookie($security->cookieName);
        $mirror = $response->getCookie($security->readableCookieName);

        $this->assertNotNull($native);
        $this->assertNotNull($mirror);
        $this->assertSame($native->getValue(), $mirror->getValue());
        $this->assertTrue($native->isHTTPOnly());
        $this->assertFalse($mirror->isHTTPOnly());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\HermeticFeatureTestCase;

/** @internal */
final class CsrfCacheTest extends HermeticFeatureTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureLocales(['es', 'en']);
    }

    public function testCachedPageResponseReceivesReadableCsrfCookieAfterPageCache(): void
    {
        $result = $this->get('es');

        $result->assertStatus(200);
        $response = $result->response();
        $this->assertTrue($response->hasCookie('csrf_cookie_name'));
        $this->assertTrue($response->hasCookie('csrf_cookie_readable'));
        $this->assertNotEmpty($result->getBody());
    }

    /**
     * Two visitors hitting the same cached snapshot page must each get their
     * own, independent CSRF cookie pair — never a shared token, and the
     * cached HTML must never contain either visitor's token verbatim.
     *
     * `pagecache` and `csrfcookie` are registered under Config\Filters'
     * `$required['after']` list specifically so csrfcookie still runs on a
     * cache HIT, which short-circuits the normal route-filter chain before
     * the controller (and any route-scoped `after` filter) ever runs — see
     * CodeIgniter::run()/runRequiredAfterFilters(). This test simulates two
     * independent visitors by resetting the 'response' and 'security'
     * services between requests: within a single PHPUnit process these are
     * otherwise shared across calls in a way a real separate HTTP request
     * (its own process/service container) never is, which would mask this
     * exact failure mode if left unreset.
     */
    public function testTwoVisitorsSharingACachedSnapshotGetIndependentCsrfCookies(): void
    {
        config('App')->pageDeliveryMode = 'snapshot';

        $first = $this->get('es');
        $first->assertStatus(200);
        $firstCookie = $first->response()->getCookie('csrf_cookie_readable');
        $this->assertNotNull($firstCookie);

        Services::injectMock('response', Services::response(null, false));
        // Force a fresh Security instance on next access — injecting null
        // makes the next getSharedInstance() lookup isset()-false and
        // rebuild, matching a real second visitor's own service container.
        Services::injectMock('security', null);

        $second = $this->get('es');
        $second->assertStatus(200);
        $secondResponse = $second->response();
        $secondCookie = $secondResponse->getCookie('csrf_cookie_readable');
        $this->assertNotNull($secondCookie);

        $this->assertSame(
            $first->getBody(),
            $second->getBody(),
            'Expected the second visitor to be served the cached snapshot body.',
        );
        $this->assertNotSame(
            $firstCookie->getValue(),
            $secondCookie->getValue(),
            'Each visitor must receive their own CSRF token, never the first visitor\'s.',
        );
        $this->assertTrue($secondResponse->hasCookie('csrf_cookie_name'));
        $this->assertStringNotContainsString(
            $firstCookie->getValue(),
            $second->getBody(),
            'A visitor\'s CSRF token must never be baked into the cached HTML body.',
        );
    }
}

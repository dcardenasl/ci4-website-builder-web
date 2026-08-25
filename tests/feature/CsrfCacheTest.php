<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\FeatureTestTrait;
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
}

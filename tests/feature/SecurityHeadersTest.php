<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\HermeticFeatureTestCase;

/**
 * Regression coverage for the nonce-based production CSP contract.
 *
 * @internal
 */
final class SecurityHeadersTest extends HermeticFeatureTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureLocales(['es', 'en']);
        config('App')->CSPEnabled = true;
    }

    public function testHtmlResponsesEmitStrictCspAndNonceAttributes(): void
    {
        $result = $this->get('/es/home');

        $result->assertStatus(200);
        $cspHeader = $result->response()->getHeaderLine('Content-Security-Policy');
        $policy = config('ContentSecurityPolicy');
        $filters = config('Filters');

        $this->assertStringContainsString("base-uri 'self'", $cspHeader);
        $this->assertStringContainsString("frame-ancestors 'none'", $cspHeader);
        $this->assertSame("'self'", $policy->scriptSrc);
        $this->assertSame('self', $policy->styleSrc);
        $this->assertSame('self', $policy->styleSrcElem);
        $this->assertContains('securityheaders', $filters->required['after']);
        $this->assertNotContains('securityheaders', $filters->globals['after']);
        $this->assertMatchesRegularExpression('/<script\s+nonce="[^"]+"/', $result->response()->getBody());
    }

    public function testInlineStyleViewsReceiveStyleNonce(): void
    {
        $html = view('blocks/pricing_plan', [
            'block'  => [],
            'config' => [],
            'data'   => [
                'name'        => 'Fixture plan',
                'price'       => '$10',
                'period'      => '/month',
                'description' => 'Fixture description',
                'features'    => '<ul><li>Fixture feature</li></ul>',
                'cta_label'   => 'Choose',
                'cta_url'     => '#choose',
            ],
        ]);

        $this->assertMatchesRegularExpression('/<style\s+nonce="[^"]+"/', $html);
    }
}

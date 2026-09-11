<?php

declare(strict_types=1);

namespace Tests\Unit\Filters;

use App\Filters\CorrelationIdFilter;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/** @internal */
final class CorrelationIdFilterTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset(true);
        parent::tearDown();
    }

    public function testReusesSafeIncomingRequestIdAndReturnsIt(): void
    {
        $filter = new CorrelationIdFilter();
        $request = service('request');
        $request->setHeader('X-Request-ID', 'baseline-1234');
        $response = service('response');

        $filter->before($request);
        $filter->after($request, $response);

        $this->assertSame('baseline-1234', $request->getHeaderLine('X-Request-ID'));
        $this->assertSame('baseline-1234', $response->getHeaderLine('X-Request-ID'));
    }

    public function testReplacesInvalidIncomingRequestIdWithUuid(): void
    {
        $filter = new CorrelationIdFilter();
        $request = service('request');
        $request->setHeader('X-Request-ID', 'bad id');

        $filter->before($request);
        $requestId = $request->getHeaderLine('X-Request-ID');

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $requestId,
        );
    }
}

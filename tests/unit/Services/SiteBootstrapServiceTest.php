<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\WebApiClient;
use App\Services\SiteBootstrapService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class SiteBootstrapServiceTest extends CIUnitTestCase
{
    public function testGetPageBootstrapUsesDedicatedEndpointAndCacheScope(): void
    {
        $apiClient = $this->createMock(WebApiClient::class);
        $apiClient
            ->expects($this->once())
            ->method('get')
            ->with('public/page-bootstrap/about', [], 300, 'bootstrap')
            ->willReturn([
                'ok' => true,
                'data' => [
                    'layout' => ['settings' => [], 'menus' => []],
                    'route' => ['type' => 'page', 'data' => ['slug' => 'about']],
                ],
            ]);

        $result = (new SiteBootstrapService($apiClient))->getPageBootstrap('about');

        $this->assertSame('page', $result['route']['type']);
        $this->assertSame('about', $result['route']['data']['slug']);
    }

    public function testPreviewRequestsAreNeverCachedAndForwardTheirSignature(): void
    {
        $apiClient = $this->createMock(WebApiClient::class);
        $apiClient
            ->expects($this->once())
            ->method('get')
            ->with(
                'public/page-bootstrap/draft',
                ['preview' => '1', 'preview_expires' => '123', 'preview_sig' => 'sig'],
                0,
                'bootstrap'
            )
            ->willReturn([
                'ok' => true,
                'data' => [
                    'layout' => ['settings' => [], 'menus' => []],
                    'route' => ['type' => 'page', 'data' => ['slug' => 'draft']],
                ],
            ]);

        $result = (new SiteBootstrapService($apiClient))->getPageBootstrap('draft', true, '123', 'sig');

        $this->assertSame('draft', $result['route']['data']['slug']);
    }
}

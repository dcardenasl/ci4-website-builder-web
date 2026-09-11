<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\WebApiClient;
use App\Services\SocialLinksService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class SocialLinksServiceTest extends CIUnitTestCase
{
    public function testProjectsValidSocialLinksFromComposedSettings(): void
    {
        $service = new SocialLinksService($this->createMock(WebApiClient::class));

        $links = $service->getActiveLinksFromSettings([
            'social_facebook' => 'https://facebook.com/example',
            'social_twitter' => 'not-a-url',
        ]);

        $this->assertSame([
            ['key' => 'social_facebook', 'label' => 'Facebook', 'url' => 'https://facebook.com/example'],
        ], $links);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\ViewModels\Blocks;

use App\ViewModels\Blocks\HeroBannerViewModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HeroBannerViewModelTest extends CIUnitTestCase
{
    public function testUsesConfigImageWhenPresent(): void
    {
        $vm = new HeroBannerViewModel([
            'block_config' => [
                'image' => ['url' => 'https://cdn.test/hero-banner.jpg'],
                'text_color' => '#123456',
            ],
            'block_data' => [
                'alt'        => 'Hero alt',
                'heading'    => 'About us',
                'subheading' => 'Who we are',
                'cta_label'  => 'Learn more',
                'cta_url'    => '/historia',
            ],
        ], 'es');

        $vars = $vm->vars();

        $this->assertSame('https://cdn.test/hero-banner.jpg', $vars['image']['url']);
        $this->assertSame('#123456', $vars['text_color']);
        $this->assertStringContainsString('/es/historia', $vars['cta_url']);
    }

    public function testLegacyDataImageUrlStillWorksAsFallback(): void
    {
        $vm = new HeroBannerViewModel([
            'block_data' => [
                'image_url' => 'https://cdn.test/legacy-hero.jpg',
            ],
        ], 'es');

        $vars = $vm->vars();

        $this->assertSame('https://cdn.test/legacy-hero.jpg', $vars['image']['url']);
        $this->assertSame('#ffffff', $vars['text_color'], 'Fallback image should still trigger the light text treatment');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Blocks;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HeroSliderViewTest extends CIUnitTestCase
{
    public function testHeroSliderViewExposesLayoutPositions(): void
    {
        $html = view('blocks/hero_slider', [
            'block' => [
                'children' => [
                    [
                        'block_data' => [
                            'image_url' => 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221200%22%20height%3D%22500%22%2F%3E',
                            'heading' => 'Hero title',
                            'subtitle' => 'Hero subtitle',
                            'cta_label' => 'Read more',
                            'cta_url' => '/contacto',
                        ],
                    ],
                ],
            ],
            'config' => [
                'caption_position' => 'overlay_bottom',
                'controls_position' => 'overlay_bottom',
                'autoplay' => false,
            ],
            'data' => [],
        ]);

        $this->assertStringContainsString('data-caption-position="overlay_bottom"', $html);
        $this->assertStringContainsString('data-controls-position="overlay_bottom"', $html);
        $this->assertStringContainsString('data-hero-caption-title', $html);
        $this->assertStringContainsString('Hero title', $html);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Blocks;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HeroBannerBlockEscapingTest extends CIUnitTestCase
{
    public function testCssClassBreakoutIsNeutralized(): void
    {
        $malicious = '"><script>alert(1)</script><section class="';

        $html = view('blocks/hero_banner', [
            'config' => ['css_class' => $malicious],
            'data'   => [],
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testEmptyCssClassRendersSection(): void
    {
        $html = view('blocks/hero_banner', [
            'config' => [],
            'data'   => ['heading' => 'Welcome'],
        ]);

        $this->assertStringContainsString('<section', $html);
        $this->assertStringContainsString('Welcome', $html);
    }
}

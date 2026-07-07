<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class HeroSliderViewModel extends AbstractBlockViewModel
{
    private const CAPTION_POSITIONS  = ['below', 'overlay_top', 'overlay_bottom', 'hide'];
    private const CONTROLS_POSITIONS = ['below', 'overlay_bottom'];

    public function vars(): array
    {
        $slides = $this->slides();

        $captionPosition = $this->configString('caption_position', 'below');
        if (! in_array($captionPosition, self::CAPTION_POSITIONS, true)) {
            $captionPosition = 'below';
        }

        $controlsPosition = $this->configString('controls_position', 'below');
        if (! in_array($controlsPosition, self::CONTROLS_POSITIONS, true)) {
            $controlsPosition = 'below';
        }

        return [
            'slides'            => $slides,
            'captionPosition'   => $captionPosition,
            'controlsPosition'  => $controlsPosition,
            'cssClass'          => trim($this->configString('css_class')),
            'autoplay'          => $this->configBool('autoplay', true),
            'intervalMs'        => max(1000, $this->configInt('interval', 6000)),
            'overlayPct'        => max(0, min(80, $this->configInt('overlay_opacity', 0))),
            'jsonSlides'        => (string) json_encode($slides, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
            'captionIsBelow'    => $captionPosition === 'below',
            'captionIsOverlay'  => in_array($captionPosition, ['overlay_top', 'overlay_bottom'], true),
            'controlsIsOverlay' => $controlsPosition === 'overlay_bottom',
        ];
    }

    /**
     * @return list<array{image_url: string, image_alt_text: string, heading: string, subtitle: string, cta_label: string, cta_url: string}>
     */
    public function slides(): array
    {
        $slides = [];

        foreach ($this->children() as $index => $child) {
            $childData = is_array($child['block_data'] ?? null) ? $child['block_data'] : [];
            $heading   = $this->childString($childData, 'heading');
            $imageUrl  = $this->childString($childData, 'image_url');

            $slides[] = [
                'image_url'      => $imageUrl !== ''
                    ? $imageUrl
                    : self::placeholderImage($heading !== '' ? $heading : ('Slide ' . ($index + 1))),
                'image_alt_text' => $this->childString($childData, 'image_alt_text', $heading),
                'heading'        => $heading,
                'subtitle'       => $this->childString($childData, 'subtitle'),
                'cta_label'      => $this->childString($childData, 'cta_label'),
                'cta_url'        => lang_url($this->childString($childData, 'cta_url', '#')),
            ];
        }

        return $slides;
    }

    /**
     * Inline SVG placeholder shown while a slide has no image configured.
     */
    public static function placeholderImage(string $label, string $background = '#e5e7eb', string $foreground = '#111827'): string
    {
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="500" viewBox="0 0 1200 500"><rect width="1200" height="500" fill="%s"/><text x="50%%" y="50%%" fill="%s" font-family="Arial,Helvetica,sans-serif" font-size="56" font-weight="700" text-anchor="middle" dominant-baseline="middle">%s</text></svg>',
            htmlspecialchars($background, ENT_QUOTES | ENT_XML1, 'UTF-8'),
            htmlspecialchars($foreground, ENT_QUOTES | ENT_XML1, 'UTF-8'),
            htmlspecialchars($label, ENT_QUOTES | ENT_XML1, 'UTF-8')
        );

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    /**
     * @param array<string, mixed> $childData
     */
    private function childString(array $childData, string $key, string $default = ''): string
    {
        $value = $childData[$key] ?? null;

        return is_scalar($value) ? (string) $value : $default;
    }
}

<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class HeroBannerViewModel extends AbstractBlockViewModel
{
    public function vars(): array
    {
        return [
            'image_url'   => $this->dataString('image_url'),
            'alt'         => $this->dataString('alt'),
            'heading'     => $this->dataString('heading'),
            'subheading'  => $this->dataString('subheading'),
            'cta_label'   => $this->dataString('cta_label'),
            'cta_url'       => lang_url($this->dataString('cta_url', '#')),
            'cssClass'      => trim($this->configString('css_class')),
            'text_color'    => trim($this->configString('text_color', '#ffffff')),
            'overlay_color' => trim($this->configString('overlay_color', 'rgba(15, 23, 42, 0.4)')),
        ];
    }
}

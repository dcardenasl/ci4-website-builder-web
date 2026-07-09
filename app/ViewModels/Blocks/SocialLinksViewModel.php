<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class SocialLinksViewModel extends AbstractBlockViewModel
{
    public function vars(): array
    {
        $heading         = $this->dataString('heading');
        $facebookUrl     = $this->configString('facebook_url');
        $facebookHandle  = $this->configString('facebook_handle');
        $instagramUrl    = $this->configString('instagram_url');
        $instagramHandle = $this->configString('instagram_handle');
        $twitterUrl      = $this->configString('twitter_url');
        $youtubeUrl      = $this->configString('youtube_url');
        $cssClass        = $this->configString('css_class');

        $networks = [];
        if ($facebookUrl !== '') {
            $networks[] = [
                'url'    => lang_url($facebookUrl),
                'label'  => 'Facebook',
                'handle' => $facebookHandle !== '' ? $facebookHandle : 'Facebook',
                'color'  => 'bg-[#1877F2]',
                'svg'    => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
            ];
        }
        if ($instagramUrl !== '') {
            $networks[] = [
                'url'    => lang_url($instagramUrl),
                'label'  => 'Instagram',
                'handle' => $instagramHandle !== '' ? $instagramHandle : 'Instagram',
                'color'  => 'bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400',
                'svg'    => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
            ];
        }
        if ($twitterUrl !== '') {
            $networks[] = [
                'url'    => lang_url($twitterUrl),
                'label'  => 'Twitter / X',
                'handle' => '@twitter',
                'color'  => 'bg-gray-900',
                'svg'    => '<path d="M 4 4 L 20 20 M 4 20 L 20 4" stroke-linecap="round"/>',
            ];
        }
        if ($youtubeUrl !== '') {
            $networks[] = [
                'url'    => lang_url($youtubeUrl),
                'label'  => 'YouTube',
                'handle' => 'YouTube',
                'color'  => 'bg-[#FF0000]',
                'svg'    => '<path d="m22 8-6 4 6 4V8z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
            ];
        }

        return [
            'heading'  => $heading,
            'networks' => $networks,
            'cssClass' => trim($cssClass),
        ];
    }
}

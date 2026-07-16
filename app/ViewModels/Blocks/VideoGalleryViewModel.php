<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class VideoGalleryViewModel extends AbstractBlockViewModel
{
    public function vars(): array
    {
        $rawVideos = $this->data()['videos'] ?? [];
        $rawVideos = is_array($rawVideos) ? $rawVideos : [];

        $videos = [];
        foreach ($rawVideos as $v) {
            $videoUrl = is_scalar($v['video_url'] ?? null) ? (string) $v['video_url'] : '';
            $title    = is_scalar($v['title'] ?? null) ? (string) $v['title'] : '';
            $desc     = is_scalar($v['description'] ?? null) ? (string) $v['description'] : '';
            $poster   = $this->normalizeMediaReference($v['poster'] ?? []);

            $embedUrl = VideoPlayerViewModel::embedUrl($videoUrl, false, false);

            $videos[] = [
                'videoUrl'    => $videoUrl,
                'title'       => $title,
                'description' => $desc,
                'poster'      => $poster,
                'embedUrl'    => $embedUrl,
                'isIframe'    => $embedUrl !== '',
            ];
        }

        return [
            'title'     => $this->dataString('title'),
            'subtitle'  => $this->dataString('subtitle'),
            'videos'    => $videos,
            'columns'   => $this->configString('columns', '3'),
            'cssClass'  => trim($this->configString('css_class')),
        ];
    }
}

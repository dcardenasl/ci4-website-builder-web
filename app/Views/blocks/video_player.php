<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$videoUrl = (string) ($data['video_url'] ?? '');
if ($videoUrl === '') {
    return;
}

$posterUrl = (string) ($data['poster_url'] ?? '');
$heading = (string) ($data['heading'] ?? '');

$autoplay = filter_var($config['autoplay'] ?? false, FILTER_VALIDATE_BOOL);
$mute = filter_var($config['mute'] ?? false, FILTER_VALIDATE_BOOL);
$loop = filter_var($config['loop'] ?? false, FILTER_VALIDATE_BOOL);
$aspectRatio = (string) ($config['aspect_ratio'] ?? '16/9');
$cssClass = trim((string) ($config['css_class'] ?? ''));

// Helper function to extract YouTube ID
$getYouTubeId = static function (string $url): ?string {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return null;
};

// Helper function to extract Vimeo ID
$getVimeoId = static function (string $url): ?string {
    $pattern = '/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/i';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[3];
    }
    return null;
};

$ytId = $getYouTubeId($videoUrl);
$vimeoId = $getVimeoId($videoUrl);

$embedUrl = '';
$isIframe = false;

if ($ytId !== null) {
    $isIframe = true;
    $embedUrl = "https://www.youtube.com/embed/{$ytId}?autoplay=1";
    if ($mute) $embedUrl .= '&mute=1';
    if ($loop) $embedUrl .= "&loop=1&playlist={$ytId}";
} elseif ($vimeoId !== null) {
    $isIframe = true;
    $embedUrl = "https://player.vimeo.com/video/{$vimeoId}?autoplay=1";
    if ($mute) $embedUrl .= '&muted=1';
    if ($loop) $embedUrl .= '&loop=1';
}

$aspectRatioClass = 'aspect-video'; // Default 16/9
if ($aspectRatio === '4/3') {
    $aspectRatioClass = 'aspect-[4/3]';
} elseif ($aspectRatio === 'auto') {
    $aspectRatioClass = 'aspect-auto';
}

$uniqueId = 'video_' . uniqid();
?>

<section class="py-8 <?= esc($cssClass) ?>">
    <div class="max-w-5xl mx-auto">
        <?php if ($heading !== ''): ?>
            <h3 class="text-xl md:text-2xl font-bold text-slate-800 mb-4 tracking-tight text-center">
                <?= esc($heading) ?>
            </h3>
        <?php endif; ?>

        <div 
            id="<?= $uniqueId ?>"
            class="relative overflow-hidden rounded-3xl bg-slate-900 shadow-md group/video <?= $aspectRatioClass ?>"
            data-video-player
            data-embed-url="<?= esc($embedUrl) ?>"
            data-is-iframe="<?= $isIframe ? '1' : '0' ?>"
            data-native-url="<?= esc($videoUrl) ?>"
            data-autoplay="<?= $autoplay ? '1' : '0' ?>"
            data-mute="<?= $mute ? '1' : '0' ?>"
            data-loop="<?= $loop ? '1' : '0' ?>"
        >
            <?php if ($posterUrl !== ''): ?>
                <!-- Lazy Load Poster View -->
                <div class="absolute inset-0 z-10 cursor-pointer flex items-center justify-center transition-all duration-300" data-poster-overlay>
                    <img 
                        src="<?= esc($posterUrl) ?>" 
                        alt="<?= esc($heading !== '' ? $heading : 'Video Poster') ?>"
                        class="absolute inset-0 w-full h-full object-cover group-hover/video:scale-[1.01] transition-transform duration-500"
                    />
                    <!-- Overlay Dark Mask -->
                    <div class="absolute inset-0 bg-slate-950/40 group-hover/video:bg-slate-950/30 transition-colors duration-300"></div>
                    
                    <!-- Pulsing Play Button -->
                    <button 
                        class="relative z-20 flex items-center justify-center w-16 h-16 md:w-20 md:h-20 rounded-full bg-white text-violet-600 shadow-lg transition-all duration-300 group-hover/video:scale-110 group-hover/video:bg-violet-600 group-hover/video:text-white"
                        aria-label="Reproducir video"
                        data-play-button
                    >
                        <!-- Pulse Ring Effect -->
                        <span class="absolute inset-0 rounded-full bg-white/30 animate-ping group-hover/video:bg-violet-600/30"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-play ml-1"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Fallback Content placeholder when poster is not present (renders iframe instantly) -->
            <?php if ($posterUrl === ''): ?>
                <?php if ($isIframe): ?>
                    <iframe 
                        src="<?= esc(str_replace('autoplay=1', 'autoplay=' . ($autoplay ? '1' : '0'), $embedUrl)) ?>"
                        class="w-full h-full border-0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                    ></iframe>
                <?php else: ?>
                    <video 
                        src="<?= esc($videoUrl) ?>"
                        class="w-full h-full object-contain"
                        controls
                        <?= $autoplay ? 'autoplay' : '' ?>
                        <?= $mute ? 'muted' : '' ?>
                        <?= $loop ? 'loop' : '' ?>
                    ></video>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- On-demand Video Loading Script -->
<?php if ($posterUrl !== ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const player = document.getElementById('<?= $uniqueId ?>');
            if (!player) return;

            const overlay = player.querySelector('[data-poster-overlay]');
            
            overlay.addEventListener('click', () => {
                const embedUrl = player.getAttribute('data-embed-url') || '';
                const isIframe = player.getAttribute('data-is-iframe') === '1';
                const nativeUrl = player.getAttribute('data-native-url') || '';
                const autoplay = player.getAttribute('data-autoplay') === '1';
                const mute = player.getAttribute('data-mute') === '1';
                const loop = player.getAttribute('data-loop') === '1';
                
                let content = '';
                
                if (isIframe) {
                    content = `<iframe src="${embedUrl}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                } else {
                    content = `<video src="${nativeUrl}" class="w-full h-full object-contain" controls autoplay ${mute ? 'muted' : ''} ${loop ? 'loop' : ''}></video>`;
                }
                
                // Animate overlay out, then inject video
                overlay.style.opacity = '0';
                setTimeout(() => {
                    overlay.remove();
                    player.innerHTML = content;
                }, 300);
            });
        });
    </script>
<?php endif; ?>

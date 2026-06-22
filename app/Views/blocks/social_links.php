<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$heading         = $data['heading'] ?? '';
$facebookUrl     = $config['facebook_url'] ?? '';
$facebookHandle  = $config['facebook_handle'] ?? '';
$instagramUrl    = $config['instagram_url'] ?? '';
$instagramHandle = $config['instagram_handle'] ?? '';
$twitterUrl      = $config['twitter_url'] ?? '';
$youtubeUrl      = $config['youtube_url'] ?? '';
$cssClass        = $config['css_class'] ?? '';

$networks = [];
if ($facebookUrl) {
    $networks[] = [
        'url'    => $facebookUrl,
        'label'  => 'Facebook',
        'handle' => $facebookHandle ?: 'Facebook',
        'color'  => 'bg-[#1877F2]',
        'svg'    => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
    ];
}
if ($instagramUrl) {
    $networks[] = [
        'url'    => $instagramUrl,
        'label'  => 'Instagram',
        'handle' => $instagramHandle ?: 'Instagram',
        'color'  => 'bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400',
        'svg'    => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
    ];
}
if ($twitterUrl) {
    $networks[] = [
        'url'    => $twitterUrl,
        'label'  => 'Twitter / X',
        'handle' => '@twitter',
        'color'  => 'bg-gray-900',
        'svg'    => '<path d="M 4 4 L 20 20 M 4 20 L 20 4" stroke-linecap="round"/>',
    ];
}
if ($youtubeUrl) {
    $networks[] = [
        'url'    => $youtubeUrl,
        'label'  => 'YouTube',
        'handle' => 'YouTube',
        'color'  => 'bg-[#FF0000]',
        'svg'    => '<path d="m22 8-6 4 6 4V8z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
    ];
}

if ($networks === []) {
    return;
}
?>
<section class="py-10 sm:py-12 <?= esc($cssClass) ?>">
    <div class="container-base">
        <div class="space-y-5">
            <?php if ($heading): ?>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                    <?= esc($heading) ?>
                </h2>
            <?php endif; ?>

            <div class="flex flex-wrap gap-3">
                <?php foreach ($networks as $network): ?>
                    <a href="<?= esc($network['url']) ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:border-slate-300 hover:text-primary">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full <?= esc($network['color']) ?> text-white">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <?= $network['svg'] ?>
                            </svg>
                        </span>
                        <span><?= esc($network['label']) ?></span>
                        <span class="text-slate-400"><?= esc($network['handle']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

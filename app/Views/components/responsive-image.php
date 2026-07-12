<?php
/**
 * Responsive Image Component
 *
 * Renders an optimized image with lazy loading, error handling, and accessibility.
 *
 * @var string $src      Image URL (required)
 * @var string $alt      Alt text (required for accessibility)
 * @var string $class    Additional CSS classes (optional)
 * @var string $loading  Loading strategy: 'lazy' (default) or 'eager'
 * @var bool   $decoding Async decoding (default: true)
 */

$src = $src ?? '';
$alt = $alt ?? '';
$class = $class ?? '';
$decoding = $decoding ?? 'async';

if ($src === '') {
    return;
}

// Dynamically optimize performance based on image sequence order and device type
$imgIndex = \Config\Services::blockRenderer()->incrementImageCount();
$isMobile = \Config\Services::request()->getUserAgent()->isMobile();
$maxEager = $isMobile ? 1 : 4;
$loading = $loading ?? (($imgIndex <= $maxEager) ? 'eager' : 'lazy');
$fetchPriority = $fetchPriority ?? (($imgIndex === 1) ? 'high' : null);

$srcsetString = '';
$sizesString = '';

// Check if the URL is from picsum.photos and has the format: https://picsum.photos/id/1040/1200/900
if (preg_match('#^https?://picsum\.photos/id/(\d+)/(\d+)/(\d+)(/?\?.*)?$#i', $src, $matches)) {
    $id = $matches[1];
    $width = (int) $matches[2];
    $height = (int) $matches[3];
    $query = $matches[4] ?? '';

    if ($width > 0 && $height > 0) {
        $ratio = $width / $height;
        $standardWidths = [480, 800, 1200];

        // Filter out widths larger than the original to avoid upscaling
        $widths = array_filter($standardWidths, static fn($w) => $w <= $width);

        // Ensure the original width is included
        if (empty($widths) || !in_array($width, $widths, true)) {
            $widths[] = $width;
        }
        sort($widths);

        $srcsetItems = [];
        foreach ($widths as $w) {
            $h = (int) round($w / $ratio);
            $srcsetItems[] = "https://picsum.photos/id/{$id}/{$w}/{$h}{$query} {$w}w";
        }

        $srcsetString = implode(', ', $srcsetItems);
        $sizesString = '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, ' . $width . 'px';
    }
}
?>
<img
    src="<?= esc($src) ?>"
    alt="<?= esc($alt) ?>"
    class="<?= esc($class) ?>"
    loading="<?= esc($loading) ?>"
    decoding="<?= esc($decoding) ?>"
    <?php if ($srcsetString !== ''): ?>
        srcset="<?= esc($srcsetString) ?>"
        sizes="<?= esc($sizesString) ?>"
    <?php endif; ?>
    <?php if ($fetchPriority !== null): ?>
        fetchpriority="<?= esc($fetchPriority) ?>"
    <?php endif; ?>
    onerror="this.classList.add('opacity-50'); this.style.objectFit='contain'; this.alt='<?= esc(lang('Site.image_failed_to_load')) ?>';"
>

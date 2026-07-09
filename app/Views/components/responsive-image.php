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
$loading = $loading ?? 'lazy';
$decoding = $decoding ?? 'async';

if ($src === '' || $alt === '') {
    return;
}
?>
<img
    src="<?= esc($src) ?>"
    alt="<?= esc($alt) ?>"
    class="<?= esc($class) ?>"
    loading="<?= esc($loading) ?>"
    decoding="<?= esc($decoding) ?>"
    onerror="this.classList.add('opacity-50'); this.style.objectFit='contain'; this.alt='<?= esc(lang('Site.image_failed_to_load')) ?>';"
>

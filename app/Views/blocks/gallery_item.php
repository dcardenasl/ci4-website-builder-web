<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $data */

$imageUrl = (string) ($data['image_url'] ?? '');
$alt = (string) ($data['alt'] ?? '');
$caption = (string) ($data['caption'] ?? '');

if ($imageUrl === '') {
    return;
}
?>

<div 
    x-init="
        const index = images.length;
        images.push({
            url: '<?= esc($imageUrl, 'js') ?>',
            alt: '<?= esc($alt, 'js') ?>',
            caption: '<?= esc($caption, 'js') ?>'
        });
    "
    @click="openLightbox(index)"
    class="group aspect-square relative rounded-2xl overflow-hidden bg-slate-100 border border-slate-200/40 shadow-sm cursor-pointer hover:shadow-lg transition-all duration-300"
>
    <!-- Image -->
    <img 
        src="<?= esc($imageUrl) ?>" 
        alt="<?= esc($alt) ?>" 
        class="w-full h-full object-cover transition-transform duration-500 scale-100 group-hover:scale-105"
        loading="lazy"
    />

    <!-- Overlay on Hover -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
        <div class="text-white min-w-0">
            <?php if ($caption !== ''): ?>
                <p class="font-semibold text-sm truncate"><?= esc($caption) ?></p>
            <?php endif; ?>
            <p class="text-xs text-white/80 flex items-center gap-1 mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zoom-in"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/></svg>
                Ver
            </p>
        </div>
    </div>
</div>

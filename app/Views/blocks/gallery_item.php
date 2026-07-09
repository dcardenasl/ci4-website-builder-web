<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $data */

$imageUrl = (string) ($data['image_url'] ?? '');
$alt = (string) ($data['alt'] ?? '');
$caption = (string) ($data['caption'] ?? '');
$linkUrl = (string) ($data['link_url'] ?? '');
$linkLabel = (string) ($data['link_label'] ?? '');
$hasLink = $linkUrl !== '';

if ($imageUrl === '') {
    return;
}
?>

<article
    data-gallery-item
    data-gallery-url="<?= esc($imageUrl) ?>"
    data-gallery-alt="<?= esc($alt) ?>"
    data-gallery-caption="<?= esc($caption) ?>"
    data-gallery-link-url="<?= esc($linkUrl) ?>"
    data-gallery-link-label="<?= esc($linkLabel) ?>"
    class="group relative aspect-square overflow-hidden rounded-2xl border border-slate-200/40 bg-slate-100 shadow-sm transition-all duration-300 <?= esc($hasLink ? 'cursor-pointer hover:shadow-lg' : '') ?>"
>
    <img
        src="<?= esc($imageUrl) ?>"
        alt="<?= esc($alt) ?>"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        loading="lazy"
    >

    <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/60 via-black/10 to-transparent p-4 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
        <div class="min-w-0 text-white">
            <?php if ($caption !== ''): ?>
                <p class="truncate text-sm font-semibold"><?= esc($caption) ?></p>
            <?php endif; ?>
            <?php if ($hasLink): ?>
                <a
                    href="<?= esc($linkUrl) ?>"
                    data-gallery-link
                    class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-white/25"
                >
                    <?= esc($linkLabel !== '' ? $linkLabel : lang('Site.gallery_view_page')) ?>
                </a>
            <?php else: ?>
                <p class="mt-0.5 flex items-center gap-1 text-xs text-white/80">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zoom-in"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/></svg>
                    Ver
                </p>
            <?php endif; ?>
        </div>
    </div>
</article>

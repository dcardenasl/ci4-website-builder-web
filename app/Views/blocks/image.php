<?php
$imageUrl = (string) ($data['image_url'] ?? $data['url'] ?? '');
$alt = (string) ($data['image_alt_text'] ?? $data['alt'] ?? '');
$caption = $data['image_caption'] ?? $data['caption'] ?? null;
$figureClass = trim((string) ($config['css_class'] ?? ''));

if ($imageUrl === ''): ?>
    <figure class="<?= esc($figureClass) ?> rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
        <p class="font-medium text-slate-600"><?= esc($alt !== '' ? $alt : lang('Site.block_image_placeholder')) ?></p>
        <?php if (! empty($caption)): ?>
            <p class="mt-2 text-slate-500"><?= esc((string) $caption) ?></p>
        <?php endif; ?>
    </figure>
<?php else: ?>
    <figure class="<?= esc($figureClass) ?>">
        <img
            src="<?= esc($imageUrl) ?>"
            alt="<?= esc($alt) ?>"
            class="w-full h-auto"
            loading="lazy"
            decoding="async"
        />
        <?php if (! empty($caption)): ?>
            <figcaption class="mt-2 text-center text-sm text-gray-600">
                <?= esc((string) $caption) ?>
            </figcaption>
        <?php endif; ?>
    </figure>
<?php endif; ?>

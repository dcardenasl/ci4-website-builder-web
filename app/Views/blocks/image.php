<figure class="<?= $config['css_class'] ?? '' ?>">
    <img
        src="<?= esc($data['image_url'] ?? '') ?>"
        alt="<?= esc($data['image_alt_text'] ?? $data['alt'] ?? '') ?>"
        class="w-full h-auto"
    />
    <?php $caption = $data['image_caption'] ?? $data['caption'] ?? null; ?>
    <?php if (!empty($caption)): ?>
        <figcaption class="mt-2 text-center text-sm text-gray-600">
            <?= esc($caption) ?>
        </figcaption>
    <?php endif; ?>
</figure>

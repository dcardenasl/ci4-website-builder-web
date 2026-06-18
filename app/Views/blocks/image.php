<figure class="<?= $config['css_class'] ?? '' ?>">
    <img
        src="<?= esc($data['image_url'] ?? '') ?>"
        alt="<?= esc($data['image_alt_text'] ?? $data['alt'] ?? '') ?>"
        class="w-full h-auto"
    />
    <?php if (!empty($data['caption'])): ?>
        <figcaption class="mt-2 text-center text-sm text-gray-600">
            <?= esc($data['caption']) ?>
        </figcaption>
    <?php endif; ?>
</figure>

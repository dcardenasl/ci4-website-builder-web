<div class="bg-blue-600 text-white py-12 px-4 text-center <?= $config['css_class'] ?? '' ?>">
    <?php if (!empty($data['heading'])): ?>
        <h2 class="text-2xl md:text-3xl font-bold mb-4">
            <?= esc($data['heading']) ?>
        </h2>
    <?php endif; ?>

    <?php if (!empty($data['text'])): ?>
        <p class="text-lg mb-6 max-w-2xl mx-auto">
            <?= esc($data['text']) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($data['label']) && !empty($data['url'])): ?>
        <a href="<?= esc($data['url']) ?>" class="inline-block bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
            <?= esc($data['label']) ?>
        </a>
    <?php endif; ?>
</div>

<section class="relative h-96 flex items-center justify-center overflow-hidden <?= esc($config['css_class'] ?? '') ?>">
    <?php if (!empty($data['image_url'])): ?>
        <img
            src="<?= esc($data['image_url']) ?>"
            alt="<?= esc($data['alt'] ?? '') ?>"
            class="absolute inset-0 w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-black/40"></div>
    <?php endif; ?>

    <div class="relative z-10 text-center text-white px-4">
        <?php if (!empty($data['heading'])): ?>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <?= esc($data['heading']) ?>
            </h1>
        <?php endif; ?>

        <?php if (!empty($data['subheading'])): ?>
            <p class="text-lg md:text-xl mb-6">
                <?= esc($data['subheading']) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($data['cta_label']) && !empty($data['cta_url'])): ?>
            <a href="<?= esc($data['cta_url']) ?>" class="inline-block bg-white text-black px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                <?= esc($data['cta_label']) ?>
            </a>
        <?php endif; ?>
    </div>
</section>

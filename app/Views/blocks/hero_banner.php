<section class="relative h-96 flex items-center justify-center overflow-hidden <?= esc($cssClass) ?>">
    <?php if (!empty($image_url)): ?>
        <img
            src="<?= esc($image_url) ?>"
            alt="<?= esc($alt) ?>"
            class="absolute inset-0 w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-black/40"></div>
    <?php endif; ?>

    <div class="relative z-10 text-center text-white px-4">
        <?php if (!empty($heading)): ?>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <?= esc($heading) ?>
            </h1>
        <?php endif; ?>

        <?php if (!empty($subheading)): ?>
            <p class="text-lg md:text-xl mb-6">
                <?= esc($subheading) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($cta_label) && !empty($cta_url)): ?>
            <a href="<?= esc($cta_url) ?>" class="inline-block bg-white text-black px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                <?= esc($cta_label) ?>
            </a>
        <?php endif; ?>
    </div>
</section>

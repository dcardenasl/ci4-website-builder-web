<div class="container mx-auto px-4 py-12">
    <div class="mb-12">
        <h1 class="text-4xl font-bold mb-4">
            <?= esc($collection['listing_title'] ?? $collection['name'] ?? '') ?>
        </h1>
        <?php if (!empty($collection['listing_intro'])): ?>
            <div class="prose max-w-none">
                <?= $collection['listing_intro'] ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php foreach (($data ?? []) as $entry): ?>
            <article class="border rounded-lg overflow-hidden hover:shadow-lg transition">
                <?php if (!empty($entry['featured_image_url'])): ?>
                    <img src="<?= esc($entry['featured_image_url']) ?>" alt="<?= esc($entry['title'] ?? '') ?>" class="w-full h-48 object-cover">
                <?php endif; ?>

                <div class="p-4">
                    <h2 class="text-xl font-bold mb-2">
                        <a href="<?= base_url($collection['url_prefix'] . '/' . ($entry['slug'] ?? '')) ?>" class="text-blue-600 hover:text-blue-800">
                            <?= esc($entry['title'] ?? '') ?>
                        </a>
                    </h2>

                    <?php if (!empty($entry['excerpt'])): ?>
                        <p class="text-gray-600 mb-4">
                            <?= esc(substr($entry['excerpt'], 0, 150)) ?>...
                        </p>
                    <?php endif; ?>

                    <a href="<?= base_url($collection['url_prefix'] . '/' . ($entry['slug'] ?? '')) ?>" class="text-blue-600 hover:text-blue-800 font-semibold">
                        <?= lang('Site.read_more') ?>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($meta['pagination'])): ?>
        <div class="flex justify-center gap-2">
            <!-- Pagination links would go here -->
        </div>
    <?php endif; ?>
</div>

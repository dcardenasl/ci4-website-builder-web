<article class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-4xl font-bold mb-4">
            <?= esc($title ?? '') ?>
        </h1>

        <div class="text-sm text-gray-500 mb-8">
            <?php if (!empty($published_at)): ?>
                <span><?= lang('Site.published_label') ?>: <?= date('d/m/Y', strtotime($published_at)) ?></span>
            <?php endif; ?>

            <?php if (!empty($categories)): ?>
                <div class="mt-2">
                    <?php foreach ($categories as $category): ?>
                        <span class="inline-block bg-gray-200 px-3 py-1 rounded-full text-xs mr-2">
                            <?= esc($category['name'] ?? '') ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="prose max-w-none mb-8">
            <?= $renderedBlocks ?? '' ?>
        </div>

        <?php if (!empty($tags)): ?>
            <div class="border-t pt-4 mt-8">
                <h3 class="font-bold mb-2"><?= lang('Site.tags_label') ?>:</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($tags as $tag): ?>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                            <?= esc($tag['name'] ?? '') ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="border-t mt-8 pt-8">
            <a href="<?= base_url($collectionUrlPrefix ?? '') ?>" class="text-blue-600 hover:text-blue-800 font-semibold">
                <?= lang('Site.back_to_list') ?>
            </a>
        </div>
    </div>
</article>

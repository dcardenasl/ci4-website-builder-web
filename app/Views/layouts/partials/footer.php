<footer class="bg-gray-800 text-white py-12 mt-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div>
                <h3 class="text-xl font-bold mb-4">
                    <?= esc($settings['site_title'] ?? 'Website') ?>
                </h3>
                <p class="text-gray-300">
                    <?= esc($settings['site_tagline'] ?? '') ?>
                </p>
            </div>

            <div>
                <h4 class="text-lg font-bold mb-4">Menú</h4>
                <ul class="space-y-2">
                    <?php foreach (($menu['items'] ?? []) as $item): ?>
                        <li>
                            <a href="<?= esc($item['custom_url'] ?? '#') ?>" class="text-gray-300 hover:text-white transition">
                                <?= esc($item['label'] ?? '') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h4 class="text-lg font-bold mb-4">Redes Sociales</h4>
                <div class="flex gap-4">
                    <?php if (!empty($settings['social_facebook'])): ?>
                        <a href="<?= esc($settings['social_facebook']) ?>" class="text-gray-300 hover:text-white transition">
                            Facebook
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_instagram'])): ?>
                        <a href="<?= esc($settings['social_instagram']) ?>" class="text-gray-300 hover:text-white transition">
                            Instagram
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?= esc($settings['social_twitter']) ?>" class="text-gray-300 hover:text-white transition">
                            Twitter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <hr class="border-gray-700 mb-8">

        <div class="text-center text-gray-400 text-sm">
            <p><?= esc($settings['site_copyright'] ?? '© ' . date('Y') . ' All rights reserved.') ?></p>
        </div>
    </div>
</footer>

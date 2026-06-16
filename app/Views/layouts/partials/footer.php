<footer class="bg-primary-dark text-white py-12 mt-16">
    <div class="container-base">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-8">
            <div>
                <h3 class="text-xl font-bold mb-4 text-white">
                    <?= esc($settings['site_title'] ?? 'Website') ?>
                </h3>
                <p class="text-blue-100">
                    <?= esc($settings['site_tagline'] ?? '') ?>
                </p>
            </div>

            <div>
                <h4 class="text-lg font-bold mb-4">Menú</h4>
                <ul class="space-y-2">
                    <?php foreach (($menu['items'] ?? []) as $item): ?>
                        <li>
                            <a href="<?= esc($item['custom_url'] ?? '#') ?>" class="text-blue-100 hover:text-white transition-colors">
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
                        <a href="<?= esc($settings['social_facebook']) ?>" class="text-blue-100 hover:text-white transition-colors">
                            Facebook
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_instagram'])): ?>
                        <a href="<?= esc($settings['social_instagram']) ?>" class="text-blue-100 hover:text-white transition-colors">
                            Instagram
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?= esc($settings['social_twitter']) ?>" class="text-blue-100 hover:text-white transition-colors">
                            Twitter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="border-t border-blue-700 pt-8">
            <div class="text-center text-blue-100 text-sm">
                <p><?= esc($settings['site_copyright'] ?? '© ' . date('Y') . ' All rights reserved.') ?></p>
            </div>
        </div>
    </div>
</footer>

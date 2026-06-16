<header class="bg-white border-b border-gray-200">
    <nav class="container-base py-4 flex justify-between items-center">
        <a href="<?= base_url() ?>" class="text-2xl font-bold text-primary hover:text-primary-light transition-colors">
            <?= esc($settings['site_title'] ?? 'Website') ?>
        </a>

        <ul class="flex gap-8">
            <?php foreach (($menu['items'] ?? []) as $item): ?>
                <li class="relative group">
                    <a href="<?= esc($item['custom_url'] ?? '#') ?>" class="text-text-secondary hover:text-primary font-medium transition-colors py-2">
                        <?= esc($item['label'] ?? '') ?>
                    </a>

                    <?php if (!empty($item['children'])): ?>
                        <ul class="absolute left-0 mt-0 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 top-full">
                            <?php foreach ($item['children'] as $subitem): ?>
                                <li>
                                    <a href="<?= esc($subitem['custom_url'] ?? '#') ?>" class="block px-4 py-2 text-text-secondary hover:bg-background hover:text-primary transition-colors">
                                        <?= esc($subitem['label'] ?? '') ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>

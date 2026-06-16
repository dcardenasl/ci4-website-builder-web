<header class="bg-white shadow">
    <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
        <a href="<?= base_url() ?>" class="text-2xl font-bold text-blue-600">
            <?= esc($settings['site_title'] ?? 'Website') ?>
        </a>

        <ul class="flex gap-6">
            <?php foreach (($menu['items'] ?? []) as $item): ?>
                <li>
                    <a href="<?= esc($item['custom_url'] ?? '#') ?>" class="text-gray-700 hover:text-blue-600 transition">
                        <?= esc($item['label'] ?? '') ?>
                    </a>

                    <?php if (!empty($item['children'])): ?>
                        <ul class="hidden absolute top-full left-0 bg-white shadow-lg rounded-lg py-2 min-w-48">
                            <?php foreach ($item['children'] as $subitem): ?>
                                <li>
                                    <a href="<?= esc($subitem['custom_url'] ?? '#') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
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

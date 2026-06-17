<footer class="bg-slate-50 border-t border-slate-100 py-16 mt-20">
    <div class="container-base">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
            <!-- Site Info -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <?= esc($settings['site_title'] ?? 'Website') ?>
                </h3>
                <p class="text-sm text-slate-500 leading-relaxed max-w-sm">
                    <?= esc($settings['site_tagline'] ?? 'Sitio web autogestionado.') ?>
                </p>
            </div>

            <!-- Navigation Menu Links -->
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Menú</h4>
                <ul class="space-y-2.5">
                    <?php foreach (($menu['items'] ?? []) as $item): ?>
                        <li>
                            <a href="<?= esc($item['custom_url'] ?? '#') ?>" class="text-sm font-medium text-slate-600 hover:text-primary transition-colors duration-150">
                                <?= esc($item['label'] ?? '') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Social Links -->
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Redes Sociales</h4>
                <div class="flex flex-col gap-2.5">
                    <?php if (!empty($settings['social_facebook'])): ?>
                        <a href="<?= esc($settings['social_facebook']) ?>" target="_blank" rel="noopener" class="text-sm font-medium text-slate-600 hover:text-primary transition-colors duration-150 flex items-center gap-2">
                            Facebook
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_instagram'])): ?>
                        <a href="<?= esc($settings['social_instagram']) ?>" target="_blank" rel="noopener" class="text-sm font-medium text-slate-600 hover:text-primary transition-colors duration-150 flex items-center gap-2">
                            Instagram
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?= esc($settings['social_twitter']) ?>" target="_blank" rel="noopener" class="text-sm font-medium text-slate-600 hover:text-primary transition-colors duration-150 flex items-center gap-2">
                            Twitter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-slate-200/60 pt-8 mt-8">
            <div class="text-center text-xs text-slate-400">
                <p><?= esc($settings['site_copyright'] ?? '© ' . date('Y') . ' ' . ($settings['site_title'] ?? 'Website') . '. Todos los derechos reservados.') ?></p>
            </div>
        </div>
    </div>
</footer>

<footer class="bg-slate-50 border-t border-slate-100 py-16 mt-20">
    <div class="container-base">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
            <!-- Site Info -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <?php if (!empty($settings['site_footer_logo_url'])): ?>
                        <img src="<?= esc($settings['site_footer_logo_url']) ?>"
                             alt="<?= esc($settings['site_name'] ?? 'Logo') ?>"
                             class="h-10 w-auto" />
                    <?php elseif (!empty($settings['site_logo_url'])): ?>
                        <img src="<?= esc($settings['site_logo_url']) ?>"
                             alt="<?= esc($settings['site_name'] ?? 'Logo') ?>"
                             class="h-10 w-auto" />
                    <?php else: ?>
                        <span class="text-lg font-bold text-primary"><?= esc($settings['site_name'] ?? 'Site') ?></span>
                    <?php endif; ?>
                </div>
                <p class="section-copy text-sm max-w-sm">
                    <?= esc($settings['site_tagline'] ?? 'Website powered by CI4.') ?>
                </p>
            </div>

            <!-- Navigation Menu Links -->
            <div class="space-y-4">
                <h4 class="section-eyebrow"><?= lang('Site.footer_menu_label') ?></h4>
                <ul class="space-y-2.5">
                    <?php foreach (($menu['items'] ?? []) as $item): ?>
                        <li>
                            <a href="<?= esc(lang_url($item['custom_url'] ?? '#')) ?>" class="text-sm font-medium text-slate-600 hover:text-primary transition-colors duration-150">
                                <?= esc($item['label'] ?? '') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Social Links -->
            <div class="space-y-4">
                <h4 class="section-eyebrow"><?= lang('Site.footer_social_label') ?></h4>
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
<?php
$siteJsPath = FCPATH . 'assets/js/site.js';
$siteJsVersion = is_file($siteJsPath) ? (string) filemtime($siteJsPath) : (string) time();
?>
<script src="<?= base_url('assets/js/site.js?v=' . $siteJsVersion) ?>" defer></script>

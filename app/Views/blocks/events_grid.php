<?php
/**
 * events_grid block — Displays entries from a CMS collection.
 *
 * @var array<string, mixed> $block
 * @var array<string, mixed> $config
 * @var array<string, mixed> $data
 * @var string $lang
 */
$sectionTitle    = $data['section_title'] ?? '';
$sectionSubtitle = $data['section_subtitle'] ?? '';
$viewAllLabel    = $data['view_all_label'] ?? '';
$viewAllUrl      = $data['view_all_url'] ?? '';
$emptyMessage    = $data['empty_message'] ?? '';
$collectionKey   = $config['collection_key'] ?? 'events';
$itemsLimit      = max(1, (int) ($config['items_limit'] ?? 6));
$cssClass        = $config['css_class'] ?? '';

// Fetch entries from the CMS collection
$entries = [];
try {
    /** @var \App\Services\SiteEntryService $entrySvc */
    $entrySvc = service('siteEntryService');
    $result   = $entrySvc->list($lang, $collectionKey, ['limit' => $itemsLimit, 'status' => 'published']);
    $entries  = $result['data'] ?? [];
} catch (\Throwable) {
    $entries = [];
}

if ($entries === [] && $sectionTitle === '') {
    return;
}

?>
<section class="py-12 sm:py-14 <?= esc($cssClass) ?>">
    <div class="container-base">
        <?php if ($sectionTitle || $viewAllLabel): ?>
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <?php if ($sectionTitle): ?>
                        <h2 class="section-title text-2xl sm:text-3xl">
                            <?= esc($sectionTitle) ?>
                        </h2>
                    <?php endif; ?>
                    <?php if ($sectionSubtitle): ?>
                        <p class="section-copy mt-2">
                            <?= esc($sectionSubtitle) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php if ($viewAllLabel && $viewAllUrl): ?>
                    <a href="<?= esc($viewAllUrl) ?>"
                       class="text-sm font-medium text-slate-600 transition-colors hover:text-primary">
                        <?= esc($viewAllLabel) ?> &rarr;
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($entries !== []): ?>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($entries as $entry):
                    $entryData   = $entry['data'][$lang] ?? $entry['data']['es'] ?? [];
                    $entryTitle  = $entryData['title'] ?? $entryData['name'] ?? '';
                    $entryExcerpt = $entryData['excerpt'] ?? $entryData['subtitle'] ?? '';
                    $entryDate   = $entry['published_at'] ?? $entry['created_at'] ?? '';
                    $entrySlug   = $entry['slug'] ?? '';
                    $entryImage  = $entryData['image_url'] ?? $entryData['cover_url'] ?? '';
                    $entryUrl    = $entrySlug !== ''
                        ? ($viewAllUrl ? rtrim($viewAllUrl, '/') . '/' . $entrySlug : '#')
                        : ($viewAllUrl ?: '#');
                ?>
                    <article class="surface-card overflow-hidden transition-colors hover:border-slate-300">
                        <?php if ($entryImage): ?>
                            <a href="<?= esc($entryUrl) ?>">
                                <img src="<?= esc($entryImage) ?>"
                                     alt="<?= esc($entryTitle) ?>"
                                     class="h-48 w-full object-cover" />
                            </a>
                        <?php else: ?>
                            <div class="flex h-48 items-center justify-center border-b border-slate-200 bg-slate-50">
                                <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="p-5">
                            <?php if ($entryDate): ?>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">
                                    <?= esc(date('d M Y', strtotime($entryDate))) ?>
                                </p>
                            <?php endif; ?>
                            <h3 class="mt-2 text-lg font-semibold leading-tight text-slate-900">
                                <a href="<?= esc($entryUrl) ?>" class="transition-colors hover:text-slate-950">
                                    <?= esc($entryTitle) ?>
                                </a>
                            </h3>
                            <?php if ($entryExcerpt): ?>
                                <p class="section-copy mt-2 text-sm"><?= esc($entryExcerpt) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php elseif ($emptyMessage): ?>
            <div class="rounded-2xl border border-dashed border-slate-200 px-5 py-8 text-slate-500">
                <?= esc($emptyMessage) ?>
            </div>
        <?php endif; ?>
    </div>
</section>

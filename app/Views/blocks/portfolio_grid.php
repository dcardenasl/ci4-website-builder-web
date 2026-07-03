<?php
/**
 * portfolio_grid block — Displays projects/entries from a CMS portfolio collection.
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
$collectionKey   = $config['collection_key'] ?? 'portafolio';
$itemsLimit      = max(1, (int) ($config['items_limit'] ?? 6));
$cssClass        = $config['css_class'] ?? '';

// Resolve canonical URL prefix from collection config
$canonicalViewAllUrl = '';
try {
    /** @var \App\Services\SiteCollectionService $collectionSvc */
    $collectionSvc = service('siteCollectionService');
    foreach ($collectionSvc->getAll($lang) as $collection) {
        if (($collection['collection_key'] ?? '') === $collectionKey) {
            $canonicalViewAllUrl = collection_url_path($collection);
            break;
        }
    }
} catch (\Throwable) {
    $canonicalViewAllUrl = '';
}
if ($canonicalViewAllUrl === '' && $viewAllUrl !== '') {
    $canonicalViewAllUrl = $viewAllUrl;
}

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
<section class="py-16 sm:py-20 bg-slate-50/50 <?= esc($cssClass) ?>">
    <div class="max-w-6xl mx-auto px-4">
        <?php if ($sectionTitle || $sectionSubtitle || $viewAllLabel): ?>
            <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <?php if ($sectionTitle): ?>
                        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">
                            <?= esc($sectionTitle) ?>
                        </h2>
                    <?php endif; ?>
                    <?php if ($sectionSubtitle): ?>
                        <p class="text-lg text-slate-500 mt-3 max-w-2xl">
                            <?= esc($sectionSubtitle) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php if ($viewAllLabel && ($canonicalViewAllUrl !== '' || $viewAllUrl !== '')): ?>
                    <a href="<?= esc(lang_url($canonicalViewAllUrl !== '' ? $canonicalViewAllUrl : $viewAllUrl)) ?>"
                       class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-primary-dark transition-colors self-start md:self-auto group">
                        <?= esc($viewAllLabel) ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right transition-transform group-hover:translate-x-1"><line x1="5" x2="19" y1="12" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($entries !== []): ?>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($entries as $entry):
                    $entryTitle   = $entry['title'] ?? '';
                    $entryExcerpt = $entry['excerpt'] ?? '';
                    $entrySlug    = $entry['slug'] ?? '';
                    $entryImage   = $entry['featured_image_url'] ?? '';
                    $entryUrl     = $canonicalViewAllUrl !== ''
                        ? lang_url(rtrim($canonicalViewAllUrl, '/') . '/' . $entrySlug)
                        : ($viewAllUrl !== '' ? lang_url(rtrim($viewAllUrl, '/') . '/' . $entrySlug) : '#');
                ?>
                    <article class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col group hover:shadow-lg transition-all duration-300">
                        <?php if ($entryImage): ?>
                            <a href="<?= esc($entryUrl) ?>" class="block overflow-hidden aspect-[4/3] relative bg-slate-100" tabindex="-1">
                                <img src="<?= esc($entryImage) ?>"
                                     alt="<?= esc($entryTitle) ?>"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                     loading="lazy" />
                            </a>
                        <?php else: ?>
                            <div class="flex aspect-[4/3] items-center justify-center bg-slate-50 border-b border-slate-100">
                                <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.008 1.24l.885 1.77a2.25 2.25 0 0 0 2.007 1.24h1.98a2.25 2.25 0 0 0 2.007-1.24l.885-1.77a2.25 2.25 0 0 1 2.007-1.24h3.86m-18 0h18"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold leading-tight text-slate-900 group-hover:text-primary transition-colors">
                                    <a href="<?= esc($entryUrl) ?>">
                                        <?= esc($entryTitle) ?>
                                    </a>
                                </h3>
                                <?php if ($entryExcerpt): ?>
                                    <p class="text-slate-500 text-sm mt-3 leading-relaxed line-clamp-3">
                                        <?= esc($entryExcerpt) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-semibold text-primary group-hover:underline">Ver proyecto</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary transition-transform group-hover:translate-x-1"><line x1="5" x2="19" y1="12" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php elseif ($emptyMessage): ?>
            <div class="border border-dashed border-slate-300 rounded-2xl px-5 py-12 text-center text-slate-500 font-medium bg-slate-50/50">
                <?= esc($emptyMessage) ?>
            </div>
        <?php endif; ?>
    </div>
</section>

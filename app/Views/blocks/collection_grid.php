<?php
/**
 * collection_grid block — Displays entries from a configured CMS collection.
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
$collectionKey   = (string) ($config['collection_key'] ?? '');
$itemsLimit      = max(1, min(100, (int) ($config['items_limit'] ?? 3)));
$orderBy         = in_array(($config['order_by'] ?? ''), ['published_at', 'sort_order', 'created_at', 'title'], true)
    ? (string) $config['order_by']
    : 'published_at';
$orderDirection  = strtolower((string) ($config['order_direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
$layoutVariant   = in_array(($config['layout_variant'] ?? ''), ['cards', 'compact', 'portfolio'], true)
    ? (string) $config['layout_variant']
    : 'cards';
$cssClass        = $config['css_class'] ?? '';

if ($collectionKey === '') {
    return;
}

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

$entries = [];
try {
    /** @var \App\Services\SiteEntryService $entrySvc */
    $entrySvc = service('siteEntryService');
    $result   = $entrySvc->list($lang, $collectionKey, [
        'per_page'        => $itemsLimit,
        'order_by'        => $orderBy,
        'order_direction' => $orderDirection,
    ]);
    $entries  = $result['data'] ?? [];
} catch (\Throwable) {
    $entries = [];
}

if ($entries === [] && $sectionTitle === '') {
    return;
}

$sectionClass = $layoutVariant === 'portfolio'
    ? 'py-16 sm:py-20 bg-slate-50/50'
    : 'py-12 sm:py-14';
$containerClass = $layoutVariant === 'portfolio'
    ? 'max-w-6xl mx-auto px-4'
    : 'container-base';
$gridClass = match ($layoutVariant) {
    'compact'   => 'grid gap-4 sm:grid-cols-2 lg:grid-cols-4',
    'portfolio' => 'grid gap-8 sm:grid-cols-2 lg:grid-cols-3',
    default     => 'grid gap-6 md:grid-cols-3',
};
?>
<section class="<?= esc($sectionClass) ?> <?= esc((string) $cssClass) ?>">
    <div class="<?= esc($containerClass) ?>">
        <?php if ($sectionTitle || $sectionSubtitle || $viewAllLabel): ?>
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <?php if ($sectionTitle): ?>
                        <h2 class="section-title text-2xl sm:text-3xl"><?= esc($sectionTitle) ?></h2>
                    <?php endif; ?>
                    <?php if ($sectionSubtitle): ?>
                        <p class="section-copy mt-2"><?= esc($sectionSubtitle) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($viewAllLabel && $canonicalViewAllUrl !== ''): ?>
                    <a href="<?= esc(lang_url($canonicalViewAllUrl)) ?>"
                       class="text-sm font-medium text-slate-600 transition-colors hover:text-primary">
                        <?= esc($viewAllLabel) ?> &rarr;
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($entries !== []): ?>
            <div class="<?= esc($gridClass) ?>">
                <?php foreach ($entries as $entry):
                    $entryTitle   = $entry['title'] ?? '';
                    $entryExcerpt = $entry['excerpt'] ?? '';
                    $entryDate    = $entry['published_at'] ?? $entry['created_at'] ?? '';
                    $entrySlug    = $entry['slug'] ?? '';
                    $entryImage   = $entry['featured_image_url'] ?? '';
                    $entryUrl     = $canonicalViewAllUrl !== '' && $entrySlug !== ''
                        ? lang_url(rtrim($canonicalViewAllUrl, '/') . '/' . $entrySlug)
                        : '#';
                    $articleClass = $layoutVariant === 'portfolio'
                        ? 'bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col group hover:shadow-lg transition-all duration-300'
                        : 'surface-card overflow-hidden transition-colors hover:border-slate-300 group';
                    $imageClass = $layoutVariant === 'portfolio' ? 'aspect-[4/3]' : 'aspect-video';
                    $bodyClass = $layoutVariant === 'portfolio' ? 'p-6' : 'p-5';
                ?>
                    <article class="<?= esc($articleClass) ?>">
                        <?php if ($entryImage): ?>
                            <a href="<?= esc($entryUrl) ?>" class="block overflow-hidden <?= esc($imageClass) ?>" tabindex="-1">
                                <img src="<?= esc($entryImage) ?>"
                                     alt="<?= esc($entryTitle) ?>"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                     loading="lazy" />
                            </a>
                        <?php endif; ?>
                        <div class="<?= esc($bodyClass) ?>">
                            <?php if ($entryDate): ?>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">
                                    <?= esc(date('d M Y', strtotime($entryDate))) ?>
                                </p>
                            <?php endif; ?>
                            <h3 class="mt-2 text-lg font-semibold leading-tight text-slate-900">
                                <a href="<?= esc($entryUrl) ?>" class="transition-colors hover:text-primary">
                                    <?= esc($entryTitle) ?>
                                </a>
                            </h3>
                            <?php if ($entryExcerpt): ?>
                                <p class="section-copy mt-2 text-sm <?= $layoutVariant === 'compact' ? 'line-clamp-1' : 'line-clamp-3' ?>">
                                    <?= esc($entryExcerpt) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php elseif ($emptyMessage): ?>
            <div class="surface-default border-dashed px-5 py-8 text-slate-500">
                <?= esc($emptyMessage) ?>
            </div>
        <?php endif; ?>
    </div>
</section>

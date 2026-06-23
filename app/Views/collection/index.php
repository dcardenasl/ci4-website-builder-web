<?php
/**
 * Collection index — blog-style listing.
 *
 * @var array<string, mixed> $collection
 * @var array<int, array<string, mixed>> $data   Entries list
 * @var array<string, mixed> $meta               API meta
 * @var array<int, array<string, mixed>> $categories
 * @var string $currentCategory  Active category slug
 * @var int $currentPage
 * @var array<string, mixed> $pagination
 * @var string $lang
 */
$urlPrefix       = $collectionUrlPrefix ?? collection_url_prefix($collection);
$listingTitle    = $collection['listing_title'] ?? $collection['name'] ?? '';
$listingIntro    = $collection['listing_intro'] ?? '';
$pagination      = $pagination ?? $meta['pagination'] ?? [];
$totalPages      = (int) ($pagination['total_pages'] ?? 1);
$allLabel        = ($lang === 'en') ? 'All' : 'Todos';
$prevLabel       = ($lang === 'en') ? '← Previous' : '← Anterior';
$nextLabel       = ($lang === 'en') ? 'Next →' : 'Siguiente →';
$emptyMsg        = ($lang === 'en') ? 'No news available yet.' : 'No hay noticias disponibles aún.';
$moreNewsLabel   = ($lang === 'en') ? 'More news' : 'Más noticias';

// Separate featured entry from the rest (only on first page with no category filter)
$featuredEntry = null;
$regularData   = $data;

if ($currentPage === 1 && $currentCategory === '' && !empty($data)) {
    foreach ($data as $i => $entry) {
        if (!empty($entry['is_featured'])) {
            $featuredEntry = $entry;
            unset($regularData[$i]);
            $regularData = array_values($regularData);
            break;
        }
    }
}

// Build query string helper
$buildUrl = static function (array $params) use ($urlPrefix): string {
    $qs = http_build_query(array_filter($params, static fn ($v) => $v !== '' && $v !== null && $v !== 0 && $v !== 1 || $v === 1));
    return lang_url($urlPrefix) . ($qs !== '' ? '?' . $qs : '');
};
?>

<!-- ── Page Header ─────────────────────────────────────────────────── -->
<section class="section-sm bg-white border-b border-slate-100">
    <div class="container-base">
        <h1 class="section-title text-3xl sm:text-4xl">
            <?= esc($listingTitle) ?>
        </h1>
        <?php if ($listingIntro): ?>
            <div class="section-copy mt-3 prose max-w-none">
                <?= $listingIntro ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── Category Filter ────────────────────────────────────────────── -->
<?php if (!empty($categories)): ?>
    <nav class="bg-white border-b border-slate-100 sticky top-0 z-10 shadow-sm" aria-label="Filtro por categoría">
        <div class="container-base">
            <div class="flex gap-2 overflow-x-auto py-3 scrollbar-none">
                <a href="<?= esc($buildUrl(['page' => null])) ?>"
                   class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors
                          <?= $currentCategory === '' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <?= esc($allLabel) ?>
                </a>
                <?php foreach ($categories as $cat): ?>
                    <?php $catSlug = $cat['slug'] ?? ''; ?>
                    <a href="<?= esc($buildUrl(['category' => $catSlug, 'page' => null])) ?>"
                       class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors
                              <?= $currentCategory === $catSlug ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                        <?= esc($cat['name'] ?? $catSlug) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
<?php endif; ?>

<div class="section bg-background">
    <div class="container-base">

        <!-- ── Featured Post ──────────────────────────────────────────── -->
        <?php if ($featuredEntry !== null):
            $fSlug    = $featuredEntry['slug'] ?? '';
            $fTitle   = $featuredEntry['title'] ?? '';
            $fExcerpt = $featuredEntry['excerpt'] ?? '';
            $fDate    = $featuredEntry['published_at'] ?? '';
            $fImage   = $featuredEntry['featured_image_url'] ?? '';
            $fCats    = array_slice($featuredEntry['categories'] ?? [], 0, 2);
            $fUrl     = lang_url($urlPrefix . '/' . $fSlug);
        ?>
            <article class="surface-card overflow-hidden mb-12 md:flex group hover:shadow-md transition-shadow">

                <?php if ($fImage): ?>
                    <a href="<?= esc($fUrl) ?>" class="block md:w-3/5 overflow-hidden aspect-video md:aspect-auto" tabindex="-1">
                        <img src="<?= esc($fImage) ?>"
                             alt="<?= esc($fTitle) ?>"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </a>
                <?php else: ?>
                    <div class="md:w-3/5 aspect-video md:aspect-auto bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                        <svg class="w-16 h-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                <?php endif; ?>

                <div class="p-6 md:p-8 md:w-2/5 flex flex-col justify-center">
                    <?php if (!empty($fCats)): ?>
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <?php foreach ($fCats as $cat): ?>
                                <span class="badge badge-secondary text-xs"><?= esc($cat['name'] ?? '') ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($fDate): ?>
                        <p class="text-xs text-text-muted uppercase tracking-widest mb-3">
                            <?= esc(date('d M Y', strtotime($fDate))) ?>
                        </p>
                    <?php endif; ?>

                    <h2 class="text-2xl font-bold text-text-primary leading-tight mb-3">
                        <a href="<?= esc($fUrl) ?>" class="hover:text-primary transition-colors">
                            <?= esc($fTitle) ?>
                        </a>
                    </h2>

                    <?php if ($fExcerpt): ?>
                        <p class="text-text-secondary mb-5 line-clamp-3">
                            <?= esc($fExcerpt) ?>
                        </p>
                    <?php endif; ?>

                    <a href="<?= esc($fUrl) ?>"
                       class="link font-semibold inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                        <?= ($lang === 'en') ? 'Read more' : 'Leer más' ?> &rarr;
                    </a>
                </div>
            </article>
        <?php endif; ?>

        <!-- ── Entries Grid ───────────────────────────────────────────── -->
        <?php if (!empty($regularData)): ?>
            <div class="grid-cols-blog grid gap-6 mb-10">
                <?php foreach ($regularData as $entry): ?>
                    <?= view('collection/partials/entry_card', [
                        'entry'               => $entry,
                        'collectionUrlPrefix' => $urlPrefix,
                        'lang'                => $lang,
                    ]) ?>
                <?php endforeach; ?>
            </div>
        <?php elseif ($featuredEntry === null): ?>
            <div class="surface-default border-dashed text-center py-16 text-text-muted">
                <svg class="mx-auto mb-4 h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <?= esc($emptyMsg) ?>
            </div>
        <?php endif; ?>

        <!-- ── Pagination ──────────────────────────────────────────────── -->
        <?php if ($totalPages > 1): ?>
            <nav class="flex items-center justify-center gap-3 mt-8" aria-label="Paginación">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= esc($buildUrl(['page' => $currentPage - 1, 'category' => $currentCategory ?: null])) ?>"
                       class="btn btn-secondary btn-sm">
                        <?= esc($prevLabel) ?>
                    </a>
                <?php else: ?>
                    <span class="btn btn-secondary btn-sm opacity-40 cursor-not-allowed"><?= esc($prevLabel) ?></span>
                <?php endif; ?>

                <span class="text-sm text-text-muted">
                    <?= ($lang === 'en') ? "Page {$currentPage} of {$totalPages}" : "Página {$currentPage} de {$totalPages}" ?>
                </span>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= esc($buildUrl(['page' => $currentPage + 1, 'category' => $currentCategory ?: null])) ?>"
                       class="btn btn-secondary btn-sm">
                        <?= esc($nextLabel) ?>
                    </a>
                <?php else: ?>
                    <span class="btn btn-secondary btn-sm opacity-40 cursor-not-allowed"><?= esc($nextLabel) ?></span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    </div>
</div>

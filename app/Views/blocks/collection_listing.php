<?php
/**
 * collection_listing block — all variables prepared by CollectionListingViewModel
 *
 * @var bool $isValid
 * @var array<string, mixed>|null $collection
 * @var string $collectionKey
 * @var string $collectionUrlPath
 * @var array<string, string> $localizedUrls
 * @var list<array<string, mixed>> $entries
 * @var array<string, mixed> $pagination
 * @var int $currentPage
 * @var string $currentCategory
 * @var string $currentTag
 * @var string $currentQuery
 * @var string $orderBy
 * @var string $orderDirection
 * @var string $layoutVariant
 * @var string $cssClass
 * @var bool $showSearch
 * @var bool $showCategories
 * @var bool $showTags
 * @var string $emptyMessage
 * @var string $introTitle
 * @var string $introText
 * @var list<array<string, mixed>> $categories
 * @var list<array<string, mixed>> $tags
 * @var string $pageTitle
 * @var string $metaDescription
 */

if (! $isValid || $collection === null) {
    return;
}

$totalPages = (int) ($pagination['total_pages'] ?? 1);
$currentPage = max(1, (int) $currentPage);
$basePath = $collectionUrlPath !== '' ? $collectionUrlPath : '';

$buildUrl = static function (array $params) use ($basePath): string {
    $clean = array_filter(
        $params,
        static fn ($value) => $value !== null && $value !== '' && $value !== 0 && $value !== false
    );

    $query = http_build_query($clean);

    return $basePath !== ''
        ? lang_url($basePath) . ($query !== '' ? '?' . $query : '')
        : '#';
};

$filterLabel = lang('Site.collection_filter');
$resetLabel = lang('Site.collection_reset');
$allLabel = lang('Site.collection_all');
$categoriesLabel = lang('Site.collection_categories');
$tagsLabel = lang('Site.collection_tags');
$previousLabel = $lang === 'en' ? 'Previous' : 'Anterior';
$nextLabel = $lang === 'en' ? 'Next' : 'Siguiente';
$noResultsLabel = lang('Site.collection_empty');

$gridClass = match ($layoutVariant) {
    'compact' => 'grid gap-4 sm:grid-cols-2 lg:grid-cols-4',
    'portfolio' => 'grid gap-8 sm:grid-cols-2 lg:grid-cols-3',
    default => 'grid gap-6 md:grid-cols-2 xl:grid-cols-3',
};

$cardClass = match ($layoutVariant) {
    'portfolio' => 'bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col group hover:shadow-lg transition-all duration-300',
    default => 'surface-card overflow-hidden transition-colors hover:border-slate-300 group flex flex-col',
};

$imageClass = $layoutVariant === 'portfolio' ? 'aspect-[4/3]' : 'aspect-video';
$bodyClass = $layoutVariant === 'portfolio' ? 'p-6' : 'p-5';
?>
<section class="<?= esc($cssClass) ?> py-10 sm:py-14">
    <div class="container-base">
        <header class="max-w-4xl">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary"><?= esc(lang('Site.collection_index_label')) ?></p>
            <h2 class="mt-3 section-title text-3xl sm:text-4xl"><?= esc($pageTitle ?: ($collection['listing_title'] ?? $collection['name'] ?? '')) ?></h2>
            <?php if ($introText !== ''): ?>
                <div class="section-copy mt-4 prose max-w-none">
                    <?= $introText ?>
                </div>
            <?php elseif (! empty($collection['listing_intro'])): ?>
                <div class="section-copy mt-4 prose max-w-none">
                    <?= $collection['listing_intro'] ?>
                </div>
            <?php endif; ?>
            <?php if ($introTitle !== ''): ?>
                <p class="mt-4 text-sm font-medium text-slate-700"><?= esc($introTitle) ?></p>
            <?php endif; ?>
        </header>

        <?php if ($showSearch || $showCategories || $showTags): ?>
            <form method="get" action="<?= esc(lang_url($basePath)) ?>" class="mt-10 rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm backdrop-blur-sm sm:p-7">
                <div class="flex flex-col gap-1 border-b border-slate-100 pb-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500"><?= esc(lang('Site.collection_filters')) ?></p>
                    <p class="text-sm text-slate-500"><?= esc(lang('Site.collection_filters_hint')) ?></p>
                </div>

                <div class="mt-6 space-y-5">
                    <?php if ($showSearch): ?>
                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500"><?= esc(lang('Site.search')) ?></span>
                            <input type="search"
                                   name="q"
                                   value="<?= esc($currentQuery, 'attr') ?>"
                                   placeholder="<?= esc(lang('Site.search')) ?>"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        </label>
                    <?php endif; ?>

                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-start">
                        <button type="submit" class="inline-flex w-full max-w-full items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-dark sm:w-max sm:max-w-none sm:flex-none">
                            <?= esc($filterLabel) ?>
                        </button>
                        <a href="<?= esc(lang_url($basePath)) ?>" class="inline-flex w-full max-w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-text-secondary shadow-sm transition-colors hover:border-slate-300 hover:bg-slate-50 sm:w-max sm:max-w-none sm:flex-none">
                            <?= esc($resetLabel) ?>
                        </a>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <input type="hidden" name="order_by" value="<?= esc($orderBy, 'attr') ?>">
                        <input type="hidden" name="order_direction" value="<?= esc($orderDirection, 'attr') ?>">
                        <input type="hidden" name="per_page" value="<?= esc((string) ((int) ($pagination['per_page'] ?? 12)), 'attr') ?>">
                        <?php if ($currentCategory !== ''): ?>
                            <input type="hidden" name="category" value="<?= esc($currentCategory, 'attr') ?>">
                        <?php endif; ?>
                        <?php if ($currentTag !== ''): ?>
                            <input type="hidden" name="tag" value="<?= esc($currentTag, 'attr') ?>">
                        <?php endif; ?>

                        <?php if ($showCategories && $categories !== []): ?>
                            <div>
                                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500"><?= esc($categoriesLabel) ?></span>
                                <div class="flex flex-nowrap gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                    <a href="<?= esc($buildUrl(['q' => $currentQuery !== '' ? $currentQuery : null, 'tag' => $currentTag !== '' ? $currentTag : null, 'order_by' => $orderBy, 'order_direction' => $orderDirection, 'per_page' => (int) ($pagination['per_page'] ?? 12)])) ?>"
                                       class="<?= esc($currentCategory === '' ? 'border border-primary/20 bg-primary/5 text-primary shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200') ?> inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-semibold transition-colors whitespace-nowrap shrink-0">
                                        <?= esc($allLabel) ?>
                                    </a>
                                    <?php foreach ($categories as $category): ?>
                                        <?php $active = $currentCategory === (string) ($category['slug'] ?? ''); ?>
                                        <a href="<?= esc((string) ($category['url'] ?? '#')) ?>"
                                           class="<?= esc($active ? 'border border-primary/20 bg-primary/5 text-primary shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200') ?> inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-semibold transition-colors whitespace-nowrap shrink-0">
                                            <?= esc((string) ($category['name'] ?? $category['slug'] ?? '')) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($showTags && $tags !== []): ?>
                            <div>
                                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500"><?= esc($tagsLabel) ?></span>
                                <div class="flex flex-nowrap gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                    <?php foreach ($tags as $tag): ?>
                                        <?php $active = $currentTag === (string) ($tag['slug'] ?? ''); ?>
                                        <a href="<?= esc((string) ($tag['url'] ?? '#')) ?>"
                                           class="<?= esc($active ? 'border border-primary/20 bg-primary/5 text-primary shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200') ?> inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-semibold transition-colors whitespace-nowrap shrink-0">
                                            <?= esc((string) ($tag['name'] ?? $tag['slug'] ?? '')) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($entries !== []): ?>
            <div class="mt-20">
                <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500"><?= esc(lang('Site.collection_listing_section')) ?></p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900"><?= esc(lang('Site.collection_listing_title')) ?></h2>
                    </div>
                    <p class="text-sm font-medium text-slate-500">
                        <?= esc(str_replace('{count}', (string) count($entries), lang('Site.collection_listing_count'))) ?>
                    </p>
                </div>

                <div class="<?= esc($gridClass) ?> mt-10 gap-y-8">
                <?php foreach ($entries as $entry):
                    $entryTitle = (string) ($entry['title'] ?? '');
                    $entryExcerpt = (string) ($entry['excerpt'] ?? '');
                    $entryDate = (string) ($entry['published_at'] ?? $entry['created_at'] ?? '');
                    $entrySlug = (string) ($entry['slug'] ?? '');
                    $entryImage = (string) ($entry['featured_image_url'] ?? '');
                    $entryUrl = $basePath !== '' && $entrySlug !== ''
                        ? lang_url(rtrim($basePath, '/') . '/' . ltrim($entrySlug, '/'))
                        : '#';
                ?>
                    <article class="<?= esc($cardClass) ?>">
                        <?php if ($entryImage !== ''): ?>
                            <a href="<?= esc($entryUrl) ?>" class="block overflow-hidden <?= esc($imageClass) ?>" tabindex="-1" aria-hidden="true">
                                <img src="<?= esc($entryImage) ?>"
                                     alt="<?= esc($entryTitle) ?>"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                     loading="lazy">
                            </a>
                        <?php else: ?>
                            <div class="relative overflow-hidden <?= esc($imageClass) ?> bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200">
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.95),transparent_55%)]"></div>
                                <div class="relative flex h-full w-full items-end p-5">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"><?= esc(lang('Site.collection_listing_item')) ?></p>
                                        <p class="mt-1 text-base font-semibold text-slate-700 line-clamp-2"><?= esc($entryTitle !== '' ? $entryTitle : lang('Site.collection_listing_featured')) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="<?= esc($bodyClass) ?>">
                            <?php if ($entryDate !== ''): ?>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">
                                    <?= esc(date('d M Y', strtotime($entryDate))) ?>
                                </p>
                            <?php endif; ?>
                            <h2 class="mt-2 text-lg font-semibold leading-tight text-slate-900">
                                <a href="<?= esc($entryUrl) ?>" class="transition-colors hover:text-primary">
                                    <?= esc($entryTitle) ?>
                                </a>
                            </h2>
                            <?php if ($entryExcerpt !== ''): ?>
                                <p class="section-copy mt-2 text-sm <?= $layoutVariant === 'compact' ? 'line-clamp-1' : 'line-clamp-3' ?>">
                                    <?= esc($entryExcerpt) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="surface-default mt-14 border-dashed px-5 py-10 text-slate-500">
                <?= esc($emptyMessage !== '' ? $emptyMessage : $noResultsLabel) ?>
            </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-12 flex flex-wrap items-center justify-center gap-3" aria-label="<?= esc(lang('Site.pagination')) ?>">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= esc($buildUrl(['page' => $currentPage - 1, 'category' => $currentCategory !== '' ? $currentCategory : null, 'tag' => $currentTag !== '' ? $currentTag : null, 'q' => $currentQuery !== '' ? $currentQuery : null, 'order_by' => $orderBy, 'order_direction' => $orderDirection, 'per_page' => (int) ($pagination['per_page'] ?? 12)])) ?>"
                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-text-secondary shadow-sm transition-colors hover:border-slate-300 hover:bg-slate-50">
                        <?= esc($previousLabel) ?>
                    </a>
                <?php endif; ?>

                <span class="text-sm text-slate-500">
                    <?= esc((string) ($pagination['current_page'] ?? $currentPage)) ?> / <?= esc((string) $totalPages) ?>
                </span>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= esc($buildUrl(['page' => $currentPage + 1, 'category' => $currentCategory !== '' ? $currentCategory : null, 'tag' => $currentTag !== '' ? $currentTag : null, 'q' => $currentQuery !== '' ? $currentQuery : null, 'order_by' => $orderBy, 'order_direction' => $orderDirection, 'per_page' => (int) ($pagination['per_page'] ?? 12)])) ?>"
                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-text-secondary shadow-sm transition-colors hover:border-slate-300 hover:bg-slate-50">
                        <?= esc($nextLabel) ?>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>

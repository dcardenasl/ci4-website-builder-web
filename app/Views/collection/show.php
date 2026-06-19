<?php
/**
 * Entry detail — article view.
 *
 * @var string $title
 * @var string $excerpt
 * @var string $published_at
 * @var string $featured_image_url
 * @var array<int, array<string, mixed>> $categories
 * @var array<int, array<string, mixed>> $tags
 * @var string $collectionUrlPrefix
 * @var string $collectionName
 * @var array<int, array<string, mixed>> $recentPosts
 * @var string $renderedBlocks
 * @var string $lang
 */
$backLabel    = ($lang === 'en') ? 'Back to News' : 'Volver a Noticias';
$tagsLabel    = ($lang === 'en') ? 'Tags' : 'Etiquetas';
$recentLabel  = ($lang === 'en') ? 'More news' : 'Más noticias';
$publishedLabel = ($lang === 'en') ? 'Published' : 'Publicado';
?>

<!-- ── Breadcrumb ─────────────────────────────────────────────────── -->
<div class="bg-white border-b border-slate-100">
    <div class="container-narrow py-3">
        <nav class="flex items-center gap-2 text-sm text-text-muted" aria-label="Breadcrumb">
            <a href="<?= lang_url('/') ?>" class="hover:text-primary transition-colors">
                <?= ($lang === 'en') ? 'Home' : 'Inicio' ?>
            </a>
            <span aria-hidden="true">/</span>
            <a href="<?= lang_url($collectionUrlPrefix ?? '/') ?>"
               class="hover:text-primary transition-colors">
                <?= esc($collectionName ?? (($lang === 'en') ? 'News' : 'Noticias')) ?>
            </a>
            <span aria-hidden="true">/</span>
            <span class="text-text-primary line-clamp-1 max-w-xs" aria-current="page">
                <?= esc($title) ?>
            </span>
        </nav>
    </div>
</div>

<!-- ── Article ────────────────────────────────────────────────────── -->
<article class="section bg-background">
    <div class="container-narrow">

        <!-- Header -->
        <header class="mb-8">
            <?php if (!empty($categories)): ?>
                <div class="flex flex-wrap gap-1.5 mb-4">
                    <?php foreach ($categories as $cat): ?>
                        <span class="badge badge-secondary"><?= esc($cat['name'] ?? '') ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h1 class="section-title text-3xl sm:text-4xl leading-tight mb-4">
                <?= esc($title) ?>
            </h1>

            <div class="flex items-center gap-4 text-sm text-text-muted">
                <?php if (!empty($published_at)): ?>
                    <time datetime="<?= esc($published_at) ?>">
                        <span class="sr-only"><?= esc($publishedLabel) ?>: </span>
                        <?= esc(date('d M Y', strtotime($published_at))) ?>
                    </time>
                <?php endif; ?>
            </div>
        </header>

        <!-- Featured image -->
        <?php if (!empty($featured_image_url)): ?>
            <figure class="mb-8 -mx-4 sm:mx-0 overflow-hidden sm:rounded-xl">
                <img src="<?= esc($featured_image_url) ?>"
                     alt="<?= esc($title) ?>"
                     class="w-full aspect-video object-cover">
            </figure>
        <?php endif; ?>

        <!-- Content blocks -->
        <div class="prose prose-slate max-w-none mb-10">
            <?= $renderedBlocks ?? '' ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
            <div class="divider mb-6"></div>
            <div class="flex flex-wrap items-center gap-2 mb-8">
                <span class="text-sm font-medium text-text-secondary"><?= esc($tagsLabel) ?>:</span>
                <?php foreach ($tags as $tag): ?>
                    <span class="badge"><?= esc($tag['name'] ?? '') ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Back link -->
        <div class="divider mb-8"></div>
        <a href="<?= lang_url($collectionUrlPrefix ?? '/') ?>"
           class="link inline-flex items-center gap-1 font-medium">
            &larr; <?= esc($backLabel) ?>
        </a>

    </div>
</article>

<!-- ── Recent Posts ───────────────────────────────────────────────── -->
<?php if (!empty($recentPosts)): ?>
    <section class="section-sm bg-white border-t border-slate-100">
        <div class="container-base">
            <h2 class="section-title text-2xl mb-8"><?= esc($recentLabel) ?></h2>
            <div class="grid-cols-blog grid gap-6">
                <?php foreach ($recentPosts as $post): ?>
                    <?= view('collection/partials/entry_card', [
                        'entry'               => $post,
                        'collectionUrlPrefix' => $collectionUrlPrefix ?? '',
                        'lang'                => $lang,
                    ]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

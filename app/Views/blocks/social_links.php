<?php
/** @var string $heading */
/** @var list<array{url: string, label: string, handle: string, color: string, svg: string}> $networks */
/** @var string $cssClass */

if ($networks === []) {
    return;
}
?>
<section class="py-10 sm:py-12 <?= esc($cssClass) ?>">
    <div class="container-base">
        <div class="space-y-5">
            <?php if ($heading): ?>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                    <?= esc($heading) ?>
                </h2>
            <?php endif; ?>

            <div class="flex flex-wrap gap-3">
                <?php foreach ($networks as $network): ?>
                    <a href="<?= esc($network['url']) ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:border-slate-300 hover:text-primary">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full <?= esc($network['color']) ?> text-white">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <?= $network['svg'] ?>
                            </svg>
                        </span>
                        <span><?= esc($network['label']) ?></span>
                        <span class="text-slate-400"><?= esc($network['handle']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

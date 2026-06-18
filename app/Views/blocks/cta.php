<?php $cssClass = $config['css_class'] ?? ''; ?>
<section class="py-12 sm:py-14 <?= esc($cssClass) ?>">
    <div class="container-base">
        <div class="surface-default py-8 sm:py-10">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                <div class="max-w-3xl">
                    <?php if (!empty($data['heading'])): ?>
                        <h2 class="section-title text-2xl sm:text-3xl">
                            <?= esc($data['heading']) ?>
                        </h2>
                    <?php endif; ?>

                    <?php if (!empty($data['text'])): ?>
                        <p class="section-copy mt-3 max-w-2xl text-base">
                            <?= esc($data['text']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($data['label']) && !empty($data['url'])): ?>
                    <div>
                        <a href="<?= esc($data['url']) ?>"
                           class="btn btn-primary rounded-xl px-6 py-3 text-sm font-semibold">
                            <?= esc($data['label']) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

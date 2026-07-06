<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$cards = [];
foreach ($block['children'] ?? [] as $child) {
    if (($child['block_key'] ?? '') !== 'slide_card') {
        continue;
    }
    $childData = $child['block_data'] ?? [];
    
    $cards[] = [
        'eyebrow'          => (string) ($childData['eyebrow'] ?? ''),
        'title'            => (string) ($childData['title'] ?? ''),
        'body'             => (string) ($childData['body'] ?? ''),
        'meta_title'       => (string) ($childData['meta_title'] ?? ''),
        'meta_description' => (string) ($childData['meta_description'] ?? ''),
        'image_url'        => (string) ($childData['image_url'] ?? ''),
        'rating'           => (int) ($childData['rating'] ?? 0),
        'link_url'         => (string) ($childData['link_url'] ?? ''),
        'link_label'       => (string) ($childData['link_label'] ?? ''),
    ];
}

if ($cards === []) {
    return;
}

$sectionTitle = (string) ($data['section_title'] ?? '');
$sectionSubtitle = (string) ($data['section_subtitle'] ?? '');
$layout = (string) ($config['layout'] ?? 'slider');
$autoplay = filter_var($config['autoplay'] ?? true, FILTER_VALIDATE_BOOL);
$interval = isset($config['interval']) ? max(1000, (int) $config['interval']) : 5000;
$visibleCount = min(3, max(1, (int) ($config['visible_count'] ?? 1)));
$cardVariant = (string) ($config['card_variant'] ?? 'editorial');
$cssClass = trim((string) ($config['css_class'] ?? ''));

$isSlider = $layout === 'slider';
$slideBasis = 100 / $visibleCount;
$dotCount = max(1, count($cards) - $visibleCount + 1);
$sliderWidthClass = $visibleCount === 1 ? 'max-w-4xl' : 'max-w-6xl';
?>

<section class="py-8 <?= esc($cssClass) ?>">
    <?php if ($sectionTitle !== '' || $sectionSubtitle !== ''): ?>
        <div class="container-base mb-8 text-center">
            <?php if ($sectionTitle !== ''): ?>
                <h2 class="section-title text-2xl sm:text-3xl"><?= esc($sectionTitle) ?></h2>
            <?php endif; ?>
            <?php if ($sectionSubtitle !== ''): ?>
                <p class="section-copy mx-auto mt-3 max-w-2xl text-base"><?= esc($sectionSubtitle) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($isSlider): ?>
        <div 
            class="relative <?= esc($sliderWidthClass) ?> mx-auto overflow-hidden group/slider"
            data-cards-slider
            data-autoplay="<?= $autoplay ? '1' : '0' ?>"
            data-interval="<?= esc((string) $interval) ?>"
            data-visible-count="<?= esc((string) $visibleCount) ?>"
        >
            <div class="slides-container flex transition-transform duration-500 ease-out">
                <?php foreach ($cards as $index => $t): ?>
                    <div class="flex-shrink-0 px-3" style="flex-basis: <?= esc((string) $slideBasis) ?>%;">
                        <div class="h-full bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm flex flex-col <?= $cardVariant === 'testimonial' ? 'text-center items-center' : '' ?>">
                            <?php if ($t['image_url'] !== ''): ?>
                                <img src="<?= esc($t['image_url']) ?>" alt="<?= esc($t['title'] ?: $t['meta_title']) ?>" class="mb-5 h-36 w-full rounded-2xl object-cover" loading="lazy" />
                            <?php endif; ?>
                            <?php if ($t['rating'] > 0): ?>
                                <div class="flex gap-1 mb-4 text-amber-400">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?= $i < $t['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($t['eyebrow'] !== ''): ?>
                                <p class="section-eyebrow mb-2"><?= esc($t['eyebrow']) ?></p>
                            <?php endif; ?>
                            <?php if ($t['title'] !== ''): ?>
                                <h3 class="text-xl font-bold leading-tight text-slate-900"><?= esc($t['title']) ?></h3>
                            <?php endif; ?>
                            <?php if ($t['body'] !== ''): ?>
                                <p class="section-copy mt-3 flex-grow text-sm leading-relaxed"><?= esc($t['body']) ?></p>
                            <?php endif; ?>
                            <?php if ($t['meta_title'] !== '' || $t['meta_description'] !== ''): ?>
                                <div class="mt-6 border-t border-slate-100 pt-4">
                                    <?php if ($t['meta_title'] !== ''): ?>
                                        <p class="font-bold text-slate-900"><?= esc($t['meta_title']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($t['meta_description'] !== ''): ?>
                                        <p class="text-xs font-medium text-slate-400"><?= esc($t['meta_description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($t['link_url'] !== '' && $t['link_label'] !== ''): ?>
                                <a href="<?= esc($t['link_url']) ?>" class="mt-5 inline-flex text-sm font-semibold text-primary hover:underline">
                                    <?= esc($t['link_label']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($cards) > $visibleCount): ?>
                <button 
                    data-slider-prev
                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-slate-700 w-10 h-10 rounded-full flex items-center justify-center shadow-md hover:scale-105 border border-slate-100 transition-all focus:outline-none opacity-0 group-hover/slider:opacity-100"
                    aria-label="Anterior"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button 
                    data-slider-next
                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-slate-700 w-10 h-10 rounded-full flex items-center justify-center shadow-md hover:scale-105 border border-slate-100 transition-all focus:outline-none opacity-0 group-hover/slider:opacity-100"
                    aria-label="Siguiente"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>

                <div class="flex justify-center gap-2 mt-6" data-slider-dots>
                    <?php for ($index = 0; $index < $dotCount; $index++): ?>
                        <button 
                            data-dot="<?= $index ?>"
                            class="w-2.5 h-2.5 rounded-full transition-all duration-300 <?= $index === 0 ? 'bg-violet-600 w-6' : 'bg-slate-300 hover:bg-slate-400' ?>"
                            aria-label="Ir a diapositiva <?= $index + 1 ?>"
                        ></button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-cards-slider]').forEach(slider => {
                    const container = slider.querySelector('.slides-container');
                    const slides = container.children;
                    const totalSlides = slides.length;
                    if (totalSlides <= 1) return;

                    let currentIdx = 0;
                    let timer = null;

                    const autoplay = slider.getAttribute('data-autoplay') === '1';
                    const interval = parseInt(slider.getAttribute('data-interval') || '5000', 10);
                    const visibleCount = Math.max(1, parseInt(slider.getAttribute('data-visible-count') || '1', 10));
                    const maxIndex = Math.max(0, totalSlides - visibleCount);

                    const updateSlider = (newIndex) => {
                        if (newIndex > maxIndex) {
                            currentIdx = 0;
                        } else if (newIndex < 0) {
                            currentIdx = maxIndex;
                        } else {
                            currentIdx = newIndex;
                        }
                        container.style.transform = `translateX(-${currentIdx * (100 / visibleCount)}%)`;
                        
                        const dots = slider.querySelectorAll('[data-slider-dots] button');
                        dots.forEach((dot, idx) => {
                            if (idx === currentIdx) {
                                dot.classList.add('bg-violet-600', 'w-6');
                                dot.classList.remove('bg-slate-300');
                            } else {
                                dot.classList.remove('bg-violet-600', 'w-6');
                                dot.classList.add('bg-slate-300');
                            }
                        });
                    };

                    slider.querySelector('[data-slider-prev]')?.addEventListener('click', () => {
                        resetAutoplay();
                        updateSlider(currentIdx - 1);
                    });

                    slider.querySelector('[data-slider-next]')?.addEventListener('click', () => {
                        resetAutoplay();
                        updateSlider(currentIdx + 1);
                    });

                    slider.querySelectorAll('[data-dot]').forEach(dot => {
                        dot.addEventListener('click', () => {
                            resetAutoplay();
                            const idx = parseInt(dot.getAttribute('data-dot'), 10);
                            updateSlider(idx);
                        });
                    });

                    const startAutoplay = () => {
                        if (!autoplay) return;
                        timer = setInterval(() => {
                            updateSlider(currentIdx + 1);
                        }, interval);
                    };

                    const resetAutoplay = () => {
                        if (timer) clearInterval(timer);
                        startAutoplay();
                    };

                    startAutoplay();
                });
            });
        </script>

    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <?php foreach ($cards as $t): ?>
                <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col">
                    <?php if ($t['image_url'] !== ''): ?>
                        <img src="<?= esc($t['image_url']) ?>" alt="<?= esc($t['title'] ?: $t['meta_title']) ?>" class="mb-4 h-32 w-full rounded-xl object-cover" loading="lazy" />
                    <?php endif; ?>
                    <?php if ($t['rating'] > 0): ?>
                        <div class="flex gap-1 mb-4 text-amber-400">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?= $i < $t['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($t['eyebrow'] !== ''): ?>
                        <p class="section-eyebrow mb-2"><?= esc($t['eyebrow']) ?></p>
                    <?php endif; ?>
                    <?php if ($t['title'] !== ''): ?>
                        <h3 class="text-lg font-bold text-slate-900"><?= esc($t['title']) ?></h3>
                    <?php endif; ?>
                    <?php if ($t['body'] !== ''): ?>
                        <p class="section-copy mt-3 flex-grow text-sm leading-relaxed"><?= esc($t['body']) ?></p>
                    <?php endif; ?>
                    <div class="mt-auto pt-4">
                        <?php if ($t['meta_title'] !== ''): ?>
                            <p class="font-bold text-slate-800 text-sm"><?= esc($t['meta_title']) ?></p>
                        <?php endif; ?>
                        <?php if ($t['meta_description'] !== ''): ?>
                            <p class="text-xs text-slate-400 font-medium"><?= esc($t['meta_description']) ?></p>
                        <?php endif; ?>
                        <?php if ($t['link_url'] !== '' && $t['link_label'] !== ''): ?>
                            <a href="<?= esc($t['link_url']) ?>" class="mt-3 inline-flex text-sm font-semibold text-primary hover:underline">
                                <?= esc($t['link_label']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

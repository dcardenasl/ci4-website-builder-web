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
        'quote'     => (string) ($childData['quote'] ?? ''),
        'author'    => (string) ($childData['author'] ?? ''),
        'role'      => (string) ($childData['role'] ?? ''),
        'avatar_url'=> (string) ($childData['avatar_url'] ?? ''),
        'rating'    => (int) ($childData['rating'] ?? 5),
    ];
}

if ($cards === []) {
    return;
}

$layout = (string) ($config['layout'] ?? 'slider');
$autoplay = filter_var($config['autoplay'] ?? true, FILTER_VALIDATE_BOOL);
$interval = isset($config['interval']) ? max(1000, (int) $config['interval']) : 5000;
$cssClass = trim((string) ($config['css_class'] ?? ''));

$isSlider = $layout === 'slider';
?>

<section class="py-8 <?= esc($cssClass) ?>">
    <?php if ($isSlider): ?>
        <!-- Slider Layout -->
        <div 
            class="relative max-w-4xl mx-auto overflow-hidden group/slider"
            data-cards-slider
            data-autoplay="<?= $autoplay ? '1' : '0' ?>"
            data-interval="<?= esc((string) $interval) ?>"
        >
            <div class="slides-container flex transition-transform duration-500 ease-out">
                <?php foreach ($cards as $index => $t): ?>
                    <div class="w-full flex-shrink-0 px-4 md:px-12">
                        <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-10 shadow-sm text-center flex flex-col items-center">
                            <!-- Stars -->
                            <div class="flex gap-1 mb-6 text-amber-400">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="<?= $i < $t['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <?php endfor; ?>
                            </div>

                            <!-- Quote -->
                            <blockquote class="text-lg md:text-xl text-slate-700 font-medium italic mb-8 max-w-2xl leading-relaxed">
                                "<?= esc($t['quote']) ?>"
                            </blockquote>

                            <!-- Author details -->
                            <div class="flex items-center gap-4 text-left">
                                <?php if ($t['avatar_url'] !== ''): ?>
                                    <img 
                                        src="<?= esc($t['avatar_url']) ?>" 
                                        alt="<?= esc($t['author']) ?>" 
                                        class="w-12 h-12 rounded-full object-cover border-2 border-slate-100"
                                        loading="lazy"
                                    />
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-violet-100 text-violet-700 flex items-center justify-center font-bold text-lg">
                                        <?= esc(substr($t['author'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <cite class="not-italic font-bold text-slate-900 block"><?= esc($t['author']) ?></cite>
                                    <?php if ($t['role'] !== ''): ?>
                                        <span class="text-xs text-slate-400 font-medium block mt-0.5"><?= esc($t['role']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Controls (Only if multiple cards) -->
            <?php if (count($cards) > 1): ?>
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

                <!-- Dots -->
                <div class="flex justify-center gap-2 mt-6" data-slider-dots>
                    <?php foreach ($cards as $index => $_t): ?>
                        <button 
                            data-dot="<?= $index ?>"
                            class="w-2.5 h-2.5 rounded-full transition-all duration-300 <?= $index === 0 ? 'bg-violet-600 w-6' : 'bg-slate-300 hover:bg-slate-400' ?>"
                            aria-label="Ir a diapositiva <?= $index + 1 ?>"
                        ></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Inline Javascript for slider functionality -->
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

                    const updateSlider = (newIndex) => {
                        currentIdx = (newIndex + totalSlides) % totalSlides;
                        container.style.transform = `translateX(-${currentIdx * 100}%)`;
                        
                        // Update dots
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
        <!-- Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <?php foreach ($cards as $t): ?>
                <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col">
                    <!-- Stars -->
                    <div class="flex gap-1 mb-4 text-amber-400">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?= $i < $t['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <?php endfor; ?>
                    </div>

                    <!-- Quote -->
                    <blockquote class="text-slate-600 text-sm md:text-base italic leading-relaxed mb-6 flex-grow">
                        "<?= esc($t['quote']) ?>"
                    </blockquote>

                    <!-- Author details -->
                    <div class="flex items-center gap-3 mt-auto pt-4 border-t border-slate-50">
                        <?php if ($t['avatar_url'] !== ''): ?>
                            <img 
                                src="<?= esc($t['avatar_url']) ?>" 
                                alt="<?= esc($t['author']) ?>" 
                                class="w-10 h-10 rounded-full object-cover border border-slate-100"
                                loading="lazy"
                            />
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-violet-100 text-violet-700 flex items-center justify-center font-bold text-sm">
                                <?= esc(substr($t['author'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <cite class="not-italic font-bold text-slate-800 text-sm block"><?= esc($t['author']) ?></cite>
                            <?php if ($t['role'] !== ''): ?>
                                <span class="text-xxs text-slate-400 font-medium block mt-0.5"><?= esc($t['role']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

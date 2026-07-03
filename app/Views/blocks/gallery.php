<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$columns = (string) ($config['columns'] ?? '3');
$gap = (string) ($config['gap'] ?? 'medium');
$cssClass = trim((string) ($config['css_class'] ?? ''));

// Map gap configurations
$gapClasses = [
    'none' => 'gap-0',
    'small' => 'gap-2',
    'medium' => 'gap-4 md:gap-6',
    'large' => 'gap-6 md:gap-8',
];
$gapClass = $gapClasses[$gap] ?? $gapClasses['medium'];

// Map column configurations
$colClasses = [
    '2' => 'grid-cols-1 sm:grid-cols-2',
    '3' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
    '4' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4',
    '6' => 'grid-cols-2 sm:grid-cols-3 md:grid-cols-6',
];
$colClass = $colClasses[$columns] ?? $colClasses['3'];
?>

<div 
    x-data="{ 
        isOpen: false,
        activeIndex: 0,
        images: [],
        openLightbox(index) {
            this.activeIndex = index;
            this.isOpen = true;
        },
        closeLightbox() {
            this.isOpen = false;
        },
        next() {
            this.activeIndex = (this.activeIndex + 1) % this.images.length;
        },
        prev() {
            this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length;
        }
    }"
    @keydown.escape.window="closeLightbox()"
    @keydown.arrow-right.window="if (isOpen) next()"
    @keydown.arrow-left.window="if (isOpen) prev()"
    class="max-w-6xl mx-auto my-8 px-4 <?= esc($cssClass) ?>"
>
    <!-- Gallery Grid -->
    <div class="grid <?= $colClass ?> <?= $gapClass ?>">
        <?= $renderedChildren ?>
    </div>

    <!-- Lightbox Overlay -->
    <template x-teleport="body">
        <div 
            x-show="isOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[100] flex flex-col justify-between bg-black/95 p-4 md:p-8 select-none"
            style="display: none;"
        >
            <!-- Close button -->
            <div class="flex justify-end p-2">
                <button 
                    @click="closeLightbox()" 
                    type="button" 
                    class="rounded-full bg-white/10 hover:bg-white/20 p-2 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white/50"
                    aria-label="Cerrar visor"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content Area -->
            <div class="flex-1 flex items-center justify-between gap-4 max-h-[80vh]">
                <!-- Prev Button -->
                <button 
                    @click="prev()" 
                    x-show="images.length > 1"
                    type="button" 
                    class="rounded-full bg-white/10 hover:bg-white/20 p-3 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white/50 shrink-0"
                    aria-label="Anterior"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Image Frame -->
                <div class="flex-1 flex items-center justify-center h-full relative">
                    <img 
                        :src="images[activeIndex]?.url" 
                        :alt="images[activeIndex]?.alt || ''" 
                        class="max-w-full max-h-[75vh] object-contain rounded shadow-2xl transition-all duration-300"
                    />
                </div>

                <!-- Next Button -->
                <button 
                    @click="next()" 
                    x-show="images.length > 1"
                    type="button" 
                    class="rounded-full bg-white/10 hover:bg-white/20 p-3 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white/50 shrink-0"
                    aria-label="Siguiente"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Info Bar / Caption -->
            <div class="text-center text-white/90 p-4 max-w-2xl mx-auto space-y-1">
                <p class="font-medium text-base md:text-lg" x-text="images[activeIndex]?.caption || ''"></p>
                <p class="text-xs text-white/50" x-text="`${activeIndex + 1} / ${images.length}`"></p>
            </div>
        </div>
    </template>
</div>

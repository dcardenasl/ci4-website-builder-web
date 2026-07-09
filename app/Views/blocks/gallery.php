<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$presentationMode = (string) ($config['presentation_mode'] ?? 'modal_preview');
if (! in_array($presentationMode, ['grid', 'inline_preview', 'modal_preview'], true)) {
    $presentationMode = 'modal_preview';
}

$columns = (string) ($config['columns'] ?? '3');
$gap = (string) ($config['gap'] ?? 'medium');
$cssClass = trim((string) ($config['css_class'] ?? ''));
$galleryId = uniqid('gallery_', true);

$gapClasses = [
    'none' => 'gap-0',
    'small' => 'gap-2',
    'medium' => 'gap-4 md:gap-6',
    'large' => 'gap-6 md:gap-8',
];
$gapClass = $gapClasses[$gap] ?? $gapClasses['medium'];

$colClasses = [
    '2' => 'grid-cols-1 sm:grid-cols-2',
    '3' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
    '4' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4',
    '6' => 'grid-cols-2 sm:grid-cols-3 md:grid-cols-6',
];
$colClass = $colClasses[$columns] ?? $colClasses['3'];
$shellClass = $presentationMode === 'inline_preview'
    ? 'max-w-7xl mx-auto my-8 px-4'
    : 'max-w-6xl mx-auto my-8 px-4';

$showInlinePreview = $presentationMode === 'inline_preview';
$showModalPreview = $presentationMode === 'modal_preview';
$openImageLabel = lang('Site.gallery_open_image');
$openImageCaptionLabel = lang('Site.gallery_open_image_caption');
?>

<div
    data-gallery-root
    data-gallery-id="<?= esc($galleryId) ?>"
    data-gallery-mode="<?= esc($presentationMode) ?>"
    class="<?= esc($shellClass) ?> <?= esc($cssClass) ?>"
>
    <?php if ($showInlinePreview): ?>
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.65fr)]">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="aspect-[4/3] bg-slate-100">
                    <img
                        data-gallery-preview-image
                        src=""
                        alt=""
                        class="hidden h-full w-full object-cover"
                        loading="lazy"
                    >
                    <div data-gallery-preview-empty class="flex h-full items-center justify-center p-8 text-sm text-slate-500">
                        <?= esc(lang('Site.collection_empty')) ?>
                    </div>
                </div>
                <div class="border-t border-slate-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400"><?= esc(lang('Site.gallery_inline_preview_label')) ?></p>
                    <h3 data-gallery-preview-caption class="mt-2 text-2xl font-semibold tracking-tight text-slate-900"></h3>
                    <p data-gallery-preview-alt class="mt-2 text-sm leading-6 text-slate-600"></p>
                    <div data-gallery-preview-counter class="mt-4 text-xs text-slate-400"></div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500"><?= esc(lang('Site.gallery_inline_preview_label')) ?></p>
                    <p class="mt-2 text-sm leading-6 text-slate-600"><?= esc(lang('Site.gallery_inline_preview_hint')) ?></p>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                    <?= $renderedChildren ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="grid <?= esc($colClass) ?> <?= esc($gapClass) ?>">
            <?= $renderedChildren ?>
        </div>
    <?php endif; ?>

    <?php if ($showModalPreview): ?>
        <div
            data-gallery-modal
            role="dialog"
            aria-modal="true"
            aria-hidden="true"
            class="fixed inset-0 z-[100] hidden flex-col justify-between bg-black/95 p-4 text-white select-none md:p-8"
        >
            <div class="flex justify-end p-2">
                <button
                    type="button"
                    data-gallery-close
                    class="rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50"
                    aria-label="Cerrar visor"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-1 items-center justify-between gap-4 max-h-[80vh]">
                <button
                    type="button"
                    data-gallery-prev
                    class="shrink-0 rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50"
                    aria-label="Anterior"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="relative flex h-full flex-1 items-center justify-center">
                    <img
                        data-gallery-modal-image
                        src=""
                        alt=""
                        class="max-h-[75vh] max-w-full rounded shadow-2xl object-contain transition-all duration-300"
                    >
                </div>

                <button
                    type="button"
                    data-gallery-next
                    class="shrink-0 rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50"
                    aria-label="Siguiente"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <div class="mx-auto max-w-2xl space-y-2 p-4 text-center text-white/90">
                <p data-gallery-modal-caption class="text-base font-medium md:text-lg"></p>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <p data-gallery-modal-counter class="text-xs text-white/50"></p>
                    <a
                        data-gallery-modal-link
                        href="#"
                        class="hidden rounded-full bg-white/15 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-white/25"
                    >
                        <?= esc(lang('Site.gallery_view_page')) ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const root = document.querySelector('[data-gallery-id="<?= esc($galleryId, 'js') ?>"]');
    if (!root || root.dataset.galleryInitialized === '1') {
        return;
    }
    root.dataset.galleryInitialized = '1';

    const mode = root.dataset.galleryMode || 'modal_preview';
    const items = Array.from(root.querySelectorAll('[data-gallery-item]'));
    if (items.length === 0) {
        return;
    }

    const isInteractiveMode = mode === 'inline_preview' || mode === 'modal_preview';
    const previewActiveClasses = ['ring-2', 'ring-sky-500', 'ring-offset-2', 'ring-offset-white'];
    const defaultModalLinkLabel = root.querySelector('[data-gallery-modal-link]')?.textContent?.trim() || '';
    let previousFocusedElement = null;
    const openImageLabel = <?= json_encode($openImageLabel, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const openImageCaptionLabel = <?= json_encode($openImageCaptionLabel, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const getItemData = (index) => {
        const item = items[index];
        if (!item) {
            return null;
        }

        return {
            url: item.dataset.galleryUrl || '',
            alt: item.dataset.galleryAlt || '',
            caption: item.dataset.galleryCaption || '',
            linkUrl: item.dataset.galleryLinkUrl || '',
            linkLabel: item.dataset.galleryLinkLabel || '',
        };
    };

    const bindInteractiveItem = (item, index) => {
        if (isInteractiveMode) {
            item.setAttribute('role', 'button');
            item.setAttribute('tabindex', '0');
            item.classList.add('cursor-zoom-in');
            const data = getItemData(index);
            const ariaLabel = data && data.caption
                ? openImageCaptionLabel.replace('{caption}', data.caption)
                : openImageLabel;
            item.setAttribute('aria-label', ariaLabel);
            item.setAttribute('title', ariaLabel);
        }

        item.addEventListener('click', (event) => {
            if (event.target.closest('[data-gallery-link]')) {
                return;
            }

            const data = getItemData(index);
            if (!data) {
                return;
            }

            if (mode === 'inline_preview') {
                setInlinePreview(index, data);
                return;
            }

            if (mode === 'modal_preview') {
                openModal(index, data);
            }
        });

        item.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }
            event.preventDefault();

            const data = getItemData(index);
            if (!data) {
                return;
            }

            if (mode === 'inline_preview') {
                setInlinePreview(index, data);
                return;
            }

            if (mode === 'modal_preview') {
                openModal(index, data);
            }
        });
    };

    items.forEach((item, index) => {
        bindInteractiveItem(item, index);
    });

    if (mode === 'inline_preview') {
        const previewImage = root.querySelector('[data-gallery-preview-image]');
        const previewEmpty = root.querySelector('[data-gallery-preview-empty]');
        const previewCaption = root.querySelector('[data-gallery-preview-caption]');
        const previewAlt = root.querySelector('[data-gallery-preview-alt]');
        const previewCounter = root.querySelector('[data-gallery-preview-counter]');

        const setInlinePreview = (index, data) => {
            if (!previewImage || !previewEmpty || !previewCaption || !previewAlt || !previewCounter) {
                return;
            }

            root.querySelectorAll('[data-gallery-item]').forEach((item, itemIndex) => {
                previewActiveClasses.forEach((className) => item.classList.toggle(className, itemIndex === index));
            });

            previewImage.src = data.url;
            previewImage.alt = data.alt || '';
            previewImage.classList.remove('hidden');
            previewEmpty.classList.add('hidden');
            previewCaption.textContent = data.caption || data.alt || '';
            previewAlt.textContent = data.alt || '';
            previewCounter.textContent = `${index + 1} / ${items.length}`;
        };

        setInlinePreview(0, getItemData(0));
        return;
    }

    if (mode !== 'modal_preview') {
        return;
    }

    const modal = root.querySelector('[data-gallery-modal]');
    const modalImage = root.querySelector('[data-gallery-modal-image]');
    const modalCaption = root.querySelector('[data-gallery-modal-caption]');
    const modalCounter = root.querySelector('[data-gallery-modal-counter]');
    const modalLink = root.querySelector('[data-gallery-modal-link]');
    const closeButton = root.querySelector('[data-gallery-close]');
    const prevButton = root.querySelector('[data-gallery-prev]');
    const nextButton = root.querySelector('[data-gallery-next]');
    let activeIndex = 0;

    const renderModal = (index) => {
        const data = getItemData(index);
        if (!data || !modal || !modalImage || !modalCaption || !modalCounter || !modalLink) {
            return;
        }

        activeIndex = index;
        modalImage.src = data.url;
        modalImage.alt = data.alt || '';
        modalCaption.textContent = data.caption || '';
        modalCounter.textContent = `${index + 1} / ${items.length}`;

        if (data.linkUrl) {
            modalLink.href = data.linkUrl;
            modalLink.textContent = data.linkLabel || defaultModalLinkLabel;
            modalLink.classList.remove('hidden');
        } else {
            modalLink.classList.add('hidden');
        }
    };

    const openModal = (index, data) => {
        if (!modal || !data) {
            return;
        }

        previousFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        renderModal(index);
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        closeButton?.focus?.();
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        previousFocusedElement?.focus?.();
    };

    const step = (delta) => {
        if (items.length === 0) {
            return;
        }

        const nextIndex = (activeIndex + delta + items.length) % items.length;
        renderModal(nextIndex);
    };

    closeButton?.addEventListener('click', closeModal);
    prevButton?.addEventListener('click', () => step(-1));
    nextButton?.addEventListener('click', () => step(1));

    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (modal?.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            step(-1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            step(1);
        }
    });
})();
</script>

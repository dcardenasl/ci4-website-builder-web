<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class CollectionGridViewModel extends AbstractBlockViewModel
{
    private const ORDER_COLUMNS   = ['published_at', 'sort_order', 'created_at', 'title'];
    private const LAYOUT_VARIANTS = ['cards', 'compact', 'portfolio'];

    public function vars(): array
    {
        $collectionKey = $this->configString('collection_key');
        $itemsLimit    = max(1, min(100, $this->configInt('items_limit', 3)));

        $orderBy = $this->configString('order_by');
        if (! in_array($orderBy, self::ORDER_COLUMNS, true)) {
            $orderBy = 'published_at';
        }

        $orderDirection = strtolower($this->configString('order_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $layoutVariant = $this->configString('layout_variant');
        if (! in_array($layoutVariant, self::LAYOUT_VARIANTS, true)) {
            $layoutVariant = 'cards';
        }

        $canonicalViewAllUrl = $collectionKey !== ''
            ? $this->canonicalViewAllUrl($collectionKey, $this->dataString('view_all_url'))
            : '';

        return [
            'sectionTitle'        => $this->dataString('section_title'),
            'sectionSubtitle'     => $this->dataString('section_subtitle'),
            'viewAllLabel'        => $this->dataString('view_all_label'),
            'emptyMessage'        => $this->dataString('empty_message'),
            'collectionKey'       => $collectionKey,
            'layoutVariant'       => $layoutVariant,
            'cssClass'            => $this->configString('css_class'),
            'canonicalViewAllUrl' => $canonicalViewAllUrl,
            'entries'             => $collectionKey !== ''
                ? $this->entries($collectionKey, $itemsLimit, $orderBy, $orderDirection)
                : [],
            'sectionClass'        => $layoutVariant === 'portfolio' ? 'py-16 sm:py-20 bg-slate-50/50' : 'py-12 sm:py-14',
            'containerClass'      => $layoutVariant === 'portfolio' ? 'max-w-6xl mx-auto px-4' : 'container-base',
            'gridClass'           => match ($layoutVariant) {
                'compact'   => 'grid gap-4 sm:grid-cols-2 lg:grid-cols-4',
                'portfolio' => 'grid gap-8 sm:grid-cols-2 lg:grid-cols-3',
                default     => 'grid gap-6 md:grid-cols-3',
            },
        ];
    }

    /**
     * Canonical URL of the collection index, falling back to the manually
     * configured view_all_url when the collection is not resolvable.
     */
    private function canonicalViewAllUrl(string $collectionKey, string $fallback): string
    {
        try {
            foreach (\Config\Services::siteCollectionService()->getAll($this->lang) as $collection) {
                if (is_array($collection) && ($collection['collection_key'] ?? '') === $collectionKey) {
                    return collection_url_path($collection);
                }
            }
        } catch (\Throwable) {
            // Fall through to the manual fallback.
        }

        return $fallback;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function entries(string $collectionKey, int $itemsLimit, string $orderBy, string $orderDirection): array
    {
        try {
            $result = \Config\Services::siteEntryService()->list($this->lang, $collectionKey, [
                'per_page'        => $itemsLimit,
                'order_by'        => $orderBy,
                'order_direction' => $orderDirection,
            ]);

            $entries = $result['data'] ?? [];

            return is_array($entries) ? array_values(array_filter($entries, 'is_array')) : [];
        } catch (\Throwable) {
            return [];
        }
    }
}

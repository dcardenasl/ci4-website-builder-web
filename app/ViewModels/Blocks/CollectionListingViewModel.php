<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class CollectionListingViewModel extends AbstractBlockViewModel
{
    private const ORDER_COLUMNS = ['published_at', 'sort_order', 'created_at', 'title'];
    private const LAYOUT_VARIANTS = ['cards', 'compact', 'portfolio'];

    /** @var array<string, mixed>|null */
    private ?array $collection = null;

    public function vars(): array
    {
        $collectionId = $this->configInt('collection_id', 0);
        $collection = $this->resolveCollection($collectionId);

        if ($collection === null && str_contains(service('request')->getUri()->getPath(), 'blocks/preview')) {
            $collection = [
                'id' => 999,
                'name' => 'Colección de Ejemplo',
                'collection_key' => 'mock-collection',
                'slug' => 'mock-collection',
                'listing_title' => 'Colección de Ejemplo en Vista Previa',
                'default_meta_description' => 'Descripción de ejemplo para metadatos de la colección.',
            ];
        }

        $this->collection = $collection;

        if ($collection === null) {
            return [
                'isValid' => false,
                'entries' => [],
                'categories' => [],
                'tags' => [],
                'pagination' => [],
                'currentPage' => 1,
                'currentCategory' => '',
                'currentTag' => '',
                'currentQuery' => '',
                'orderBy' => 'published_at',
                'orderDirection' => 'desc',
                'layoutVariant' => 'cards',
                'cssClass' => $this->configString('css_class'),
                'showSearch' => $this->configBool('show_search', true),
                'showCategories' => $this->configBool('show_categories', true),
                'showTags' => $this->configBool('show_tags', false),
                'emptyMessage' => $this->dataString('empty_message'),
                'introTitle' => $this->dataString('intro_title'),
                'introText' => $this->dataString('intro_text'),
                'collection' => null,
                'collectionUrlPath' => '',
                'localizedUrls' => [],
            ];
        }

        $request = service('request');
        $currentPage = max(1, (int) ($request->getGet('page') ?: 1));
        $currentCategory = trim((string) ($request->getGet('category') ?? ''));
        $currentTag = trim((string) ($request->getGet('tag') ?? ''));
        $currentQuery = trim((string) ($request->getGet('q') ?? ''));

        $orderBy = $this->resolveOrderBy((string) ($request->getGet('order_by') ?? ''));
        $orderDirection = $this->resolveOrderDirection((string) ($request->getGet('order_direction') ?? ''));
        $perPage = max(1, min(100, $this->configInt('per_page', 12)));
        $layoutVariant = $this->resolveLayoutVariant($this->configString('layout_variant', 'cards'));
        $collectionKey = (string) ($collection['collection_key'] ?? '');
        $collectionUrlPath = localized_collection_url_path($collection, $this->lang);
        $localizedUrls = localized_collection_urls($collection);

        $query = [
            'page' => $currentPage,
            'per_page' => $perPage,
            'order_by' => $orderBy,
            'order_direction' => $orderDirection,
        ];
        if ($currentCategory !== '') {
            $query['category'] = $currentCategory;
        }
        if ($currentTag !== '') {
            $query['tag'] = $currentTag;
        }
        if ($currentQuery !== '') {
            $query['q'] = $currentQuery;
        }

        $result = [];
        try {
            $result = \Config\Services::siteEntryService()->list($this->lang, $collectionKey, $query);
        } catch (\Throwable) {
            $result = ['data' => [], 'meta' => ['pagination' => []]];
        }

        if ((empty($result['data']) || !is_array($result['data'])) && str_contains(service('request')->getUri()->getPath(), 'blocks/preview')) {
            $result = [
                'data' => [
                    [
                        'id' => 1,
                        'slug' => 'mock-entry-1',
                        'title' => 'Caso de Éxito de Ejemplo 1',
                        'summary' => 'Esta es una descripción corta para la primera entrada de ejemplo en la lista.',
                        'published_at' => date('Y-m-d H:i:s'),
                        'featured_image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80',
                        'categories' => [['title' => 'Casos', 'slug' => 'casos']],
                        'tags' => [['title' => 'Tag 1', 'slug' => 'tag-1']],
                    ],
                    [
                        'id' => 2,
                        'slug' => 'mock-entry-2',
                        'title' => 'Lanzamiento de Producto Especial',
                        'summary' => 'Esta es una descripción corta para la segunda entrada de ejemplo en la lista.',
                        'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                        'featured_image_url' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=600&q=80',
                        'categories' => [['title' => 'Productos', 'slug' => 'productos']],
                        'tags' => [['title' => 'Tag 2', 'slug' => 'tag-2']],
                    ]
                ],
                'meta' => [
                    'pagination' => [
                        'currentPage' => 1,
                        'totalPages' => 1,
                        'perPage' => 12,
                        'totalItems' => 2,
                    ]
                ]
            ];
        }

        $categories = $this->configBool('show_categories', true)
            ? $this->resolveCategories($collectionKey, $currentPage, $currentCategory, $currentTag, $currentQuery, $orderBy, $orderDirection, $perPage)
            : [];
        $tags = $this->configBool('show_tags', false)
            ? $this->resolveTags($collectionKey, $currentPage, $currentCategory, $currentTag, $currentQuery, $orderBy, $orderDirection, $perPage)
            : [];

        return [
            'isValid' => true,
            'collection' => $collection,
            'collectionUrlPath' => $collectionUrlPath,
            'localizedUrls' => $localizedUrls,
            'collectionKey' => $collectionKey,
            'entries' => is_array($result['data'] ?? null) ? array_values(array_filter($result['data'], 'is_array')) : [],
            'pagination' => is_array($result['meta']['pagination'] ?? null) ? $result['meta']['pagination'] : [],
            'currentPage' => $currentPage,
            'currentCategory' => $currentCategory,
            'currentTag' => $currentTag,
            'currentQuery' => $currentQuery,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection,
            'layoutVariant' => $layoutVariant,
            'cssClass' => $this->configString('css_class'),
            'showSearch' => $this->configBool('show_search', true),
            'showCategories' => $this->configBool('show_categories', true),
            'showTags' => $this->configBool('show_tags', false),
            'emptyMessage' => $this->dataString('empty_message', $this->defaultEmptyMessage()),
            'introTitle' => $this->dataString('intro_title'),
            'introText' => $this->dataString('intro_text'),
            'categories' => $categories,
            'tags' => $tags,
            'pageTitle' => (string) ($collection['listing_title'] ?? $collection['name'] ?? ''),
            'metaDescription' => (string) ($collection['default_meta_description'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveCollection(int $collectionId): ?array
    {
        if ($collectionId <= 0) {
            return null;
        }

        foreach (\Config\Services::siteCollectionService()->getAll($this->lang) as $collection) {
            if (! is_array($collection)) {
                continue;
            }

            if ((int) ($collection['id'] ?? 0) === $collectionId) {
                return $collection;
            }
        }

        return null;
    }

    private function resolveOrderBy(string $value): string
    {
        return in_array($value, self::ORDER_COLUMNS, true) ? $value : 'published_at';
    }

    private function resolveOrderDirection(string $value): string
    {
        return strtolower($value) === 'asc' ? 'asc' : 'desc';
    }

    private function resolveLayoutVariant(string $value): string
    {
        return in_array($value, self::LAYOUT_VARIANTS, true) ? $value : 'cards';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveCategories(string $collectionKey, int $currentPage, string $currentCategory, string $currentTag, string $currentQuery, string $orderBy, string $orderDirection, int $perPage): array
    {
        try {
            $categories = \Config\Services::siteCategoryService()->list($this->lang, $collectionKey);
        } catch (\Throwable) {
            $categories = [];
        }

        $filters = $this->baseFilterParams($currentPage, $currentCategory, $currentTag, $currentQuery, $orderBy, $orderDirection, $perPage);
        $result = [];

        foreach ($categories as $category) {
            if (! is_array($category)) {
                continue;
            }

            $slug = trim((string) ($category['slug'] ?? ''), '/');
            if ($slug === '') {
                continue;
            }

            $filters['category'] = $slug;
            $filters['tag'] = null;
            $filters['page'] = null;
            $category['url'] = $this->buildUrl($filters);
            $result[] = $category;
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveTags(string $collectionKey, int $currentPage, string $currentCategory, string $currentTag, string $currentQuery, string $orderBy, string $orderDirection, int $perPage): array
    {
        try {
            $tags = \Config\Services::siteTagService()->list($this->lang, $collectionKey);
        } catch (\Throwable) {
            $tags = [];
        }

        $filters = $this->baseFilterParams($currentPage, $currentCategory, $currentTag, $currentQuery, $orderBy, $orderDirection, $perPage);
        $result = [];

        foreach ($tags as $tag) {
            if (! is_array($tag)) {
                continue;
            }

            $slug = trim((string) ($tag['slug'] ?? ''), '/');
            if ($slug === '') {
                continue;
            }

            $filters['tag'] = $slug;
            $filters['category'] = null;
            $filters['page'] = null;
            $tag['url'] = $this->buildUrl($filters);
            $result[] = $tag;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function baseFilterParams(int $page, string $category, string $tag, string $q, string $orderBy, string $orderDirection, int $perPage): array
    {
        return [
            'page' => $page,
            'per_page' => $perPage,
            'category' => $category !== '' ? $category : null,
            'tag' => $tag !== '' ? $tag : null,
            'q' => $q !== '' ? $q : null,
            'order_by' => $orderBy !== '' ? $orderBy : null,
            'order_direction' => $orderDirection !== '' ? $orderDirection : null,
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    private function buildUrl(array $params): string
    {
        $path = localized_collection_url_path($this->collection ?? [], $this->lang);
        $query = http_build_query(array_filter($params, static fn ($value) => $value !== null && $value !== ''));

        return $path !== ''
            ? lang_url($path) . ($query !== '' ? '?' . $query : '')
            : '#';
    }

    private function defaultEmptyMessage(): string
    {
        return $this->lang === 'en'
            ? 'No items available at the moment.'
            : 'No hay contenido disponible por el momento.';
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

class SiteEntryService extends BaseSiteService
{
    private const CACHE_TTL_DETAIL = 300; // 5 minutes for single entry
    private const CACHE_TTL_LIST = 180;   // 3 minutes for list (more dynamic)

    /**
     * List entries in a collection with optional pagination and filtering.
     *
     * @param array<string, mixed> $query Query parameters: page, limit, category, tag, etc.
     *
     * @return array<string, mixed> {data: entries[], meta: {pagination: ...}}
     */
    public function list(string $lang, string $collectionKey, array $query = []): array
    {
        $response = $this->apiClient->get(
            "public/{$lang}/entries/{$collectionKey}",
            $query,
            self::CACHE_TTL_LIST,
            'entries'
        );

        if (! $response['ok']) {
            return ['data' => [], 'meta' => ['pagination' => []]];
        }

        return [
            'data' => is_array($response['data']) ? $response['data'] : [],
            'meta' => ['pagination' => $response['meta']],
        ];
    }

    /**
     * Get a single published entry by slug.
     *
     * @return array<string, mixed>|null
     */
    public function getBySlug(string $lang, string $collectionKey, string $slug): ?array
    {
        return $this->fetchData(
            "public/{$lang}/entries/{$collectionKey}/{$slug}",
            [],
            self::CACHE_TTL_DETAIL,
            'entries'
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SiteEntryService
{
    private const CACHE_TTL_DETAIL = 300; // 5 minutes for single entry
    private const CACHE_TTL_LIST = 180;   // 3 minutes for list (more dynamic)

    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * List entries in a collection with optional pagination and filtering.
     *
     * @param array<string, mixed> $query Query parameters: page, limit, category, tag, etc.
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

        if (!$response['ok'] ?? false) {
            return ['data' => [], 'meta' => ['pagination' => []]];
        }

        return [
            'data' => is_array($response['data']) ? $response['data'] : [],
            'meta' => ['pagination' => $response['meta'] ?? []],
        ];
    }

    /**
     * Get a single published entry by slug.
     *
     * @return array<string, mixed>|null
     */
    public function getBySlug(string $lang, string $collectionKey, string $slug): ?array
    {
        $response = $this->apiClient->get(
            "public/{$lang}/entries/{$collectionKey}/{$slug}",
            [],
            self::CACHE_TTL_DETAIL,
            'entries'
        );

        if (!$response['ok'] ?? false) {
            return null;
        }

        return $response['data'] ?? null;
    }
}

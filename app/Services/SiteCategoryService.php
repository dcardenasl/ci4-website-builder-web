<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SiteCategoryService
{
    private const CACHE_TTL = 600; // 10 minutes — categories change rarely

    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * List active categories for a collection.
     *
     * @return array<int, array{id: int, slug: string, name: string}>
     */
    public function list(string $lang, string $collectionKey): array
    {
        $response = $this->apiClient->get(
            "public/{$lang}/categories/{$collectionKey}",
            [],
            self::CACHE_TTL,
            'taxonomies'
        );

        if (!($response['ok'] ?? false)) {
            return [];
        }

        return $response['data']['data'] ?? $response['data'] ?? [];
    }
}

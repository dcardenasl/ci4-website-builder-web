<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SitePageService
{
    private const CACHE_TTL_DETAIL = 300; // 5 minutes for single page
    private const CACHE_TTL_LIST = 600;   // 10 minutes for list

    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * Get a published page by slug.
     *
     * @return array<string, mixed>|null
     */
    public function getBySlug(string $lang, string $slug): ?array
    {
        $response = $this->apiClient->get("public/{$lang}/pages/{$slug}", [], self::CACHE_TTL_DETAIL, 'pages');

        if (! ($response['ok'] ?? false)) {
            return null;
        }

        return $response['data'] ?? null;
    }

    /**
     * List all published pages for a language (for sitemap generation).
     *
     * @return array<array<string, mixed>>
     */
    public function listAll(string $lang): array
    {
        $response = $this->apiClient->get("public/{$lang}/pages", [], self::CACHE_TTL_LIST, 'pages');

        if (! ($response['ok'] ?? false)) {
            return [];
        }

        return $response['data'] ?? [];
    }
}

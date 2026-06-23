<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SiteCollectionService
{
    private const CACHE_TTL = 600; // 10 minutes

    /** @var array<string, array<string, mixed>>|null */
    private ?array $allCollectionsCache = null;

    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * Get all active collections for a language.
     *
     * @return array<array<string, mixed>>
     */
    public function getAll(string $lang): array
    {
        $response = $this->apiClient->get("public/{$lang}/collections", [], self::CACHE_TTL, 'collections');

        if (!$response['ok'] ?? false) {
            return [];
        }

        return $response['data'] ?? [];
    }

    /**
     * Match a collection by its url_prefix.
     * Useful for routing: e.g., match "/blog" → collection with url_prefix="/blog".
     *
     * @return array<string, mixed>|null
     */
    public function matchByPrefix(string $lang, string $urlPrefix): ?array
    {
        $collections = $this->getAll($lang);

        foreach ($collections as $collection) {
            if (($collection['url_prefix'] ?? null) === $urlPrefix) {
                return $collection;
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SiteCollectionService
{
    private const CACHE_TTL = 600; // 10 minutes

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

        if (! ($response['ok'] ?? false)) {
            return [];
        }

        return $response['data'] ?? [];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SiteMenuService
{
    private const CACHE_TTL = 600; // 10 minutes

    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * Get a menu by key with its hierarchical tree structure.
     *
     * @return array<string, mixed>
     */
    public function getMenu(string $menuKey): array
    {
        $response = $this->apiClient->get("public/menus/{$menuKey}", [], self::CACHE_TTL);

        if (!$response['ok'] ?? false) {
            return ['items' => []];
        }

        return $response['data'] ?? ['items' => []];
    }
}

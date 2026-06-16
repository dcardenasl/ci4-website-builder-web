<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SiteRedirectService
{
    private const CACHE_TTL = 3600; // 1 hour (very stable)

    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * Resolve a redirect by path.
     *
     * @return array<string, mixed>|null {new_url, redirect_type, ...}
     */
    public function resolve(string $path): ?array
    {
        $response = $this->apiClient->get("public/redirects/{$path}", [], self::CACHE_TTL);

        if (!$response['ok'] ?? false) {
            return null;
        }

        return $response['data'] ?? null;
    }
}

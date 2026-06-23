<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SiteSettingsService
{
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * Get all public settings as a key-value array.
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        $response = $this->apiClient->get('public/settings', [], self::CACHE_TTL, 'settings');

        if (!$response['ok'] ?? false) {
            return [];
        }

        return $response['data'] ?? [];
    }

    /**
     * Get a single setting by key with optional default value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->getAll();

        return $all[$key] ?? $default;
    }
}

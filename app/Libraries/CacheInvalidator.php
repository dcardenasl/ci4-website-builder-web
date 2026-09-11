<?php

declare(strict_types=1);

namespace App\Libraries;

class CacheInvalidator
{
    private const STATUS_CACHE_KEY = 'public_site_cache_invalidation_status_v1';

    /** @var list<string> */
    private const VALID_SOURCES = ['remote', 'admin_content_write', 'admin_manual'];

    /** @var list<string> */
    private const VALID_SCOPES = [
        'settings',
        'menus',
        'pages',
        'collections',
        'entries',
        'taxonomies',
        'redirects',
        'forms',
    ];

    /**
     * Delete all cached web API responses for the given content scopes.
     *
     * Uses CI4's deleteMatching() (glob-based) so only the targeted prefix is cleared,
     * never the entire cache store.
     *
     * @param list<string> $scopes
     * @return array{invalidated: list<string>, deleted: int}
     */
    public function invalidate(array $scopes, string $source = 'remote'): array
    {
        $cache        = \Config\Services::cache();
        $invalidated  = [];
        $totalDeleted = 0;

        foreach ($scopes as $scope) {
            if (! in_array($scope, self::VALID_SCOPES, true)) {
                log_message('warning', '[CacheInvalidator] Unknown scope requested: ' . $scope);
                continue;
            }

            $deleted      = $cache->deleteMatching('web_api_*_' . $scope . '_*');
            $totalDeleted += $deleted;
            $invalidated[] = $scope;

            log_message('info', "[CacheInvalidator] Scope '{$scope}': {$deleted} cache entries deleted.");

            if (in_array($scope, ['pages', 'collections', 'entries'], true)) {
                $cache->deleteMatching('sitemap_*');
            }
        }

        if (array_intersect($invalidated, ['settings', 'menus']) !== []) {
            $totalDeleted += $cache->deleteMatching('web_api_*_layout_*');
        }
        if (array_intersect($invalidated, ['pages', 'collections', 'entries']) !== []) {
            $totalDeleted += $cache->deleteMatching('web_api_*_bootstrap_*');
        }

        $responseCacheDeleted = $invalidated === []
            ? 0
            : \Config\Services::htmlResponseCacheRegistry()->invalidate($invalidated);
        $totalDeleted += $responseCacheDeleted;

        if ($invalidated !== []) {
            $this->recordStatus($invalidated, $totalDeleted, $source);
        }

        return ['invalidated' => $invalidated, 'deleted' => $totalDeleted];
    }

    /**
     * Return operational metadata for the public-site cache maintenance UI.
     *
     * The status uses a non-expiring cache entry and is intentionally small so
     * it survives ordinary response-cache expiry without exposing cache keys.
     *
     * @return array{configured: bool, handler: string, last_invalidation_at: string|null, last_invalidation_source: string|null, last_invalidation_scopes: list<string>, last_deleted: int, last_automatic_invalidation_at: string|null, last_manual_invalidation_at: string|null}
     */
    public function status(): array
    {
        $stored = \Config\Services::cache()->get(self::STATUS_CACHE_KEY);
        $stored = is_array($stored) ? $stored : [];

        return [
            'configured' => trim((string) env('CACHE_INVALIDATE_KEY', '')) !== '',
            'handler' => (string) config('Cache')->handler,
            'last_invalidation_at' => $this->nullableString($stored['last_invalidation_at'] ?? null),
            'last_invalidation_source' => $this->nullableString($stored['last_invalidation_source'] ?? null),
            'last_invalidation_scopes' => $this->stringList($stored['last_invalidation_scopes'] ?? []),
            'last_deleted' => max(0, (int) ($stored['last_deleted'] ?? 0)),
            'last_automatic_invalidation_at' => $this->nullableString($stored['last_automatic_invalidation_at'] ?? null),
            'last_manual_invalidation_at' => $this->nullableString($stored['last_manual_invalidation_at'] ?? null),
        ];
    }

    /** @param list<string> $scopes */
    private function recordStatus(array $scopes, int $deleted, string $source): void
    {
        $source = trim($source);
        $source = in_array($source, self::VALID_SOURCES, true) ? $source : 'remote';
        $now = gmdate('c');
        $status = $this->status();
        $status['last_invalidation_at'] = $now;
        $status['last_invalidation_source'] = $source;
        $status['last_invalidation_scopes'] = array_values($scopes);
        $status['last_deleted'] = max(0, $deleted);
        if ($source === 'admin_manual') {
            $status['last_manual_invalidation_at'] = $now;
        } else {
            $status['last_automatic_invalidation_at'] = $now;
        }

        \Config\Services::cache()->save(self::STATUS_CACHE_KEY, $status, 0);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value !== '' ? $value : null;
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn (mixed $item): string => trim((string) $item), $value),
            static fn (string $item): bool => $item !== '',
        ));
    }

    /** @return list<string> */
    public static function validScopes(): array
    {
        return self::VALID_SCOPES;
    }
}

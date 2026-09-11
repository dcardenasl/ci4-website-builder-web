<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Cache\CacheInterface;

/**
 * Indexes the opaque keys used by CI4's ResponseCache by public route and
 * content scope so targeted API invalidations can evict rendered HTML too.
 * The response cache remains owned by CI4; this class only stores metadata.
 */
final class HtmlResponseCacheRegistry
{
    private const KEY = 'public_html_response_index_v1';
    private const MAX_ENTRIES = 10_000;

    public function __construct(private readonly ?CacheInterface $cache = null)
    {
    }

    /**
     * Record the exact opaque response-cache key for one rendered variant.
     *
     * @param list<string> $scopes
     */
    public function record(string $path, string $locale, array $scopes, ?string $cacheKey = null): void
    {
        try {
            $path = $this->normalizePath($path);
            $locale = strtolower(trim($locale));
            $scopes = $this->normalizeList($scopes);
            $cacheKey = is_string($cacheKey) ? trim($cacheKey) : null;

            if ($path === '' || $locale === '' || $scopes === []) {
                return;
            }

            $cache = $this->cache();
            $this->withIndexLock(function () use ($cache, $path, $locale, $scopes, $cacheKey): void {
                $index = $this->readIndex($cache);
                $keys = $cacheKey !== null && $cacheKey !== ''
                    ? [$cacheKey]
                    : [md5('GET:' . $path), md5('GET:' . site_url($path))];
                $entryKey = hash('sha256', $locale . "\0" . $path . "\0" . implode("\0", $keys));
                $index[$entryKey] = [
                    'path' => $path,
                    'locale' => $locale,
                    'route' => $this->route($path, $locale),
                    'scopes' => $scopes,
                    'keys' => $keys,
                    'recorded_at' => time(),
                ];
                $cache->save(self::KEY, $this->prune($index, $cache), 0);
            });
        } catch (\Throwable $exception) {
            log_message('warning', 'HTML response-cache registry record failed: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate indexed HTML variants matching the requested scopes.
     *
     * @param list<string> $scopes
     * @param list<string> $locales
     * @param list<string> $routes
     */
    public function invalidate(array $scopes, array $locales = [], array $routes = []): int
    {
        try {
            $cache = $this->cache();
            $scopes = $this->normalizeList($scopes);
            $locales = $this->normalizeList($locales);
            $routes = array_values(array_unique(array_filter(array_map(
                static fn (mixed $route): string => strtolower(trim((string) $route, " /\t\n\r\0\x0B")),
                $routes,
            ))));
            $deleted = 0;

            $this->withIndexLock(function () use ($cache, $scopes, $locales, $routes, &$deleted): void {
                $index = $this->readIndex($cache);
                $remaining = [];

                foreach ($index as $key => $entry) {
                    if (! is_array($entry) || ! $this->matches($entry, $scopes, $locales, $routes)) {
                        $remaining[$key] = $entry;
                        continue;
                    }

                    foreach (is_array($entry['keys'] ?? null) ? $entry['keys'] : [] as $cacheKey) {
                        if (is_string($cacheKey) && $cacheKey !== '' && $cache->delete($cacheKey)) {
                            $deleted++;
                        }
                    }
                }

                $cache->save(self::KEY, $remaining, 0);
            });

            return $deleted;
        } catch (\Throwable $exception) {
            log_message('warning', 'HTML response-cache registry invalidation failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * @param array<string, mixed> $entry
     * @param list<string> $scopes
     * @param list<string> $locales
     * @param list<string> $routes
     */
    private function matches(array $entry, array $scopes, array $locales, array $routes): bool
    {
        $entryScopes = is_array($entry['scopes'] ?? null) ? $entry['scopes'] : [];
        if (array_intersect($scopes, $entryScopes) === []) {
            return false;
        }
        if ($locales !== [] && ! in_array((string) ($entry['locale'] ?? ''), $locales, true)) {
            return false;
        }
        if ($routes === []) {
            return true;
        }

        $route = trim((string) ($entry['route'] ?? ''), '/');
        $path = trim((string) ($entry['path'] ?? ''), '/');

        return in_array($route, $routes, true) || in_array($path, $routes, true);
    }

    /**
     * @param array<string, mixed> $index
     * @return array<string, mixed>
     */
    private function prune(array $index, CacheInterface $cache): array
    {
        if (count($index) <= self::MAX_ENTRIES) {
            return $index;
        }

        $live = [];
        foreach ($index as $entryKey => $entry) {
            if (! is_array($entry) || ! $this->hasLiveCacheKey($entry, $cache)) {
                continue;
            }
            $live[$entryKey] = $entry;
        }

        if (count($live) <= self::MAX_ENTRIES) {
            return $live;
        }

        uasort($live, static function (mixed $left, mixed $right): int {
            $leftTime = is_array($left) ? (int) ($left['recorded_at'] ?? 0) : 0;
            $rightTime = is_array($right) ? (int) ($right['recorded_at'] ?? 0) : 0;

            return $rightTime <=> $leftTime;
        });

        return array_slice($live, 0, self::MAX_ENTRIES, true);
    }

    /** @param array<string, mixed> $entry */
    private function hasLiveCacheKey(array $entry, CacheInterface $cache): bool
    {
        foreach (is_array($entry['keys'] ?? null) ? $entry['keys'] : [] as $key) {
            if (is_string($key) && $key !== '' && $cache->get($key) !== null) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, mixed> */
    private function readIndex(CacheInterface $cache): array
    {
        $index = $cache->get(self::KEY);

        return is_array($index) ? $index : [];
    }

    /** @param list<mixed> $values
     *  @return list<string>
     */
    private function normalizeList(array $values): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): string => is_scalar($value) ? strtolower(trim((string) $value)) : '',
            $values,
        ))));
    }

    /** @param \Closure(): void $operation */
    private function withIndexLock(\Closure $operation): void
    {
        $directory = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cache';
        if (! is_dir($directory) || ! is_writable($directory)) {
            $operation();

            return;
        }

        $handle = fopen($directory . DIRECTORY_SEPARATOR . self::KEY . '.lock', 'c');
        if ($handle === false || ! flock($handle, LOCK_EX)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            $operation();

            return;
        }

        try {
            $operation();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function route(string $path, string $locale): string
    {
        $localized = trim($path, '/');
        $prefix = trim($locale, '/') . '/';
        if (str_starts_with($localized, $prefix)) {
            $localized = substr($localized, strlen($prefix));
        }

        return $localized === '' ? 'home' : $localized;
    }

    private function normalizePath(string $path): string
    {
        $parsed = parse_url($path, PHP_URL_PATH);

        return is_string($parsed) ? '/' . trim($parsed, '/') : '';
    }

    private function cache(): CacheInterface
    {
        return $this->cache instanceof CacheInterface
            ? $this->cache
            : \Config\Services::cache();
    }
}

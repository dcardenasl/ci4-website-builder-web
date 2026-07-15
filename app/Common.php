<?php

declare(strict_types=1);

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application.
 */

if (! function_exists('lang_url')) {
    /**
     * Generate localized base URL.
     */
    function lang_url(?string $url = '', ?string $locale = null): string
    {
        if (empty($url) || $url === '#') {
            return '#';
        }

        // Return absolute URLs as is
        if (preg_match('/^(https?:)?\/\//', $url)) {
            return $url;
        }

        $currentLocale = $locale !== null && $locale !== ''
            ? $locale
            : service('request')->getLocale();
        $url = '/' . ltrim($url, '/');

        // Check if URL already has a valid locale prefix
        foreach (config('App')->supportedLocales as $locale) {
            if (strpos($url, '/' . $locale . '/') === 0 || $url === '/' . $locale) {
                return base_url($url);
            }
        }

        return base_url('/' . $currentLocale . $url);
    }
}

if (! function_exists('current_lang_url')) {
    /**
     * Get the current URL in a different locale.
     *
     * @param array<string, string>|null $localizedUrls The controller-computed
     *      per-locale URLs for the current page/entry (translated collection
     *      prefix + slug), keyed by locale. Pass the view's own `$localized_urls`
     *      explicitly — this can no longer be read back from `service('renderer')`
     *      because every `view()` call in the render chain uses `saveData: false`
     *      (required to stop page data leaking into sibling partials), which also
     *      means the renderer never persists this data for a later lookup.
     */
    function current_lang_url(string $locale, ?array $localizedUrls = null): string
    {
        if (is_array($localizedUrls) && isset($localizedUrls[$locale])) {
            $uri = service('request')->getUri();
            $query = $uri->getQuery();
            return $localizedUrls[$locale] . ($query !== '' ? '?' . $query : '');
        }

        $uri = service('request')->getUri();
        $segments = $uri->getSegments();
        $supportedLocales = config('App')->supportedLocales;

        if (!empty($segments) && in_array($segments[0], $supportedLocales, true)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        $path = implode('/', $segments);
        $query = $uri->getQuery();

        return base_url('/' . $path . ($query !== '' ? '?' . $query : ''));
    }
}

if (! function_exists('collection_resolve_text')) {
    /**
     * Resolve a collection display string from a prioritized list of fields.
     *
     * @param array<string, mixed> $collection
     * @param list<string> $fields
     */
    function collection_resolve_text(array $collection, array $fields, bool $humanizeFallback = false): string
    {
        foreach ($fields as $field) {
            $value = trim((string) ($collection[$field] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        if (! $humanizeFallback) {
            return '';
        }

        foreach (['slug', 'collection_key'] as $field) {
            $value = trim((string) ($collection[$field] ?? ''));
            if ($value === '') {
                continue;
            }

            $value = preg_replace('/[-_]+/', ' ', $value) ?? $value;

            if (function_exists('mb_convert_case')) {
                return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
            }

            return ucwords($value);
        }

        return '';
    }
}

if (! function_exists('collection_display_title')) {
    /**
     * Resolve the public title for a collection without hardcoded section names.
     *
     * @param array<string, mixed> $collection
     */
    function collection_display_title(array $collection): string
    {
        return collection_resolve_text($collection, ['listing_title', 'name'], true);
    }
}

if (! function_exists('collection_display_intro')) {
    /**
     * Resolve the public intro for a collection.
     *
     * @param array<string, mixed> $collection
     */
    function collection_display_intro(array $collection): string
    {
        return collection_resolve_text($collection, ['listing_intro', 'description'], false);
    }
}

if (! function_exists('collection_url_path')) {
    /**
     * Resolve the canonical public path for a collection payload.
     *
     * @param array<string, mixed> $collection
     */
    function collection_url_path(array $collection): string
    {
        $locale = service('request')->getLocale();

        return localized_collection_url_path($collection, $locale);
    }
}

if (! function_exists('collection_url_path_info')) {
    /**
     * Match a request path against a collection's known public prefixes.
     *
     * @param array<string, mixed> $collection
     * @return array{prefix: string, remainder: string}|null
     */
    function collection_url_path_info(array $collection, string $path): ?array
    {
        $normalizedPath = trim($path, '/');
        $prefix = trim(collection_url_path($collection), '/');
        if ($prefix === '') {
            return null;
        }

        if ($normalizedPath === $prefix) {
            return [
                'prefix' => '/' . $prefix,
                'remainder' => '',
            ];
        }

        if (str_starts_with($normalizedPath, $prefix . '/')) {
            return [
                'prefix' => '/' . $prefix,
                'remainder' => substr($normalizedPath, strlen($prefix) + 1),
            ];
        }

        return null;
    }
}

if (! function_exists('localized_collection_url_path')) {
    /**
     * Resolve the canonical public path for a collection in a given locale.
     *
     * Prefers the collection's dedicated index page slug when one is
     * published. Falls back to `/{collection_key}` when there is no index
     * page — `collection_key` is a required, URL-safe field (see
     * `CollectionModel::$validationRules`), so this always yields a stable
     * path that `PageController::resolve()`'s Step 1 (collection prefix
     * match) can route back to, regardless of which page(s) happen to embed
     * a collection_listing/collection_grid block for this collection. Do not
     * fall back to the *current request path* instead — that makes entry
     * URLs depend on which page rendered them, producing different links for
     * the same entry when two pages embed the same collection.
     *
     * @param array<string, mixed> $collection
     */
    function localized_collection_url_path(array $collection, string $locale): string
    {
        $indexPage = $collection['index_page'] ?? null;
        if (is_array($indexPage)) {
            $localizedSlugs = $indexPage['localized_slugs'] ?? [];
            if (is_array($localizedSlugs) && isset($localizedSlugs[$locale])) {
                $slug = trim((string) $localizedSlugs[$locale], '/');
                if ($slug !== '') {
                    return '/' . $slug;
                }
            }
        }

        $collectionKey = trim((string) ($collection['collection_key'] ?? ''), '/');

        return $collectionKey !== '' ? '/' . $collectionKey : '';
    }
}

if (! function_exists('localized_collection_urls')) {
    /**
     * Build language-specific URLs for a collection index page.
     *
     * @param array<string, mixed> $collection
     * @return array<string, string>
     */
    function localized_collection_urls(array $collection): array
    {
        $urls = [];
        foreach (config('App')->supportedLocales as $locale) {
            $path = localized_collection_url_path($collection, $locale);
            if ($path !== '') {
                $urls[$locale] = site_url('/' . $locale . $path);
            }
        }

        return $urls;
    }
}

if (! function_exists('localized_entry_urls')) {
    /**
     * Build language-specific URLs for an entry detail page.
     *
     * @param array<string, mixed> $collection
     * @param array<string, mixed> $entry
     * @return array<string, string>
     */
    function localized_entry_urls(array $collection, array $entry): array
    {
        $urls = [];
        $localizedSlugs = is_array($entry['localized_slugs'] ?? null) ? $entry['localized_slugs'] : [];

        foreach (config('App')->supportedLocales as $locale) {
            $collectionPath = localized_collection_url_path($collection, $locale);
            if ($collectionPath === '') {
                continue;
            }
            $slug = isset($localizedSlugs[$locale]) ? trim((string) $localizedSlugs[$locale], '/') : '';
            if ($slug !== '') {
                $urls[$locale] = site_url('/' . $locale . $collectionPath . '/' . $slug);
            } else {
                // No translation for this locale — fall back to the collection index
                $urls[$locale] = site_url('/' . $locale . $collectionPath);
            }
        }

        return $urls;
    }
}

if (! function_exists('legacy_block_text_key_usage_count')) {
    /**
     * Cumulative count of `block_text_content()` legacy-key hits, persisted
     * outside the app log so it stays visible regardless of `Logger::$threshold`
     * (production's default threshold of 4 filters out debug/info/notice
     * entirely — a plain `log_message()` call here would never surface in
     * production log files). Read it with `php spark legacy:block-text-report`.
     * TTL 0 = never expires (see CacheInterface handlers' getItem()).
     *
     * @param 'read'|'increment'|'reset' $action
     */
    function legacy_block_text_key_usage_count(string $action = 'read'): int
    {
        $cache = \Config\Services::cache();
        $key   = 'legacy_block_text_key_usage_count';

        if ($action === 'reset') {
            $cache->save($key, 0, 0);

            return 0;
        }

        if ($action === 'increment') {
            if ($cache->get($key) === null) {
                $cache->save($key, 0, 0);
            }
            $cache->increment($key);
        }

        $value = $cache->get($key);

        return is_int($value) ? $value : 0;
    }
}

if (! function_exists('block_text_content')) {
    /**
     * Resolve rich text content from a block payload using the canonical field
     * name first, then common legacy fallbacks.
     *
     * @param array<string, mixed> $data
     */
    function block_text_content(array $data, string $default = ''): string
    {
        foreach (['content', 'body', 'html'] as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];
            if (is_string($value) && trim($value) !== '') {
                if ($key !== 'content') {
                    log_message('debug', "[block_text_content] Legacy payload key '{$key}' used; source should migrate to 'content'.");
                    legacy_block_text_key_usage_count('increment');
                }

                // Second-layer sanitization (defense-in-depth): the Domain CMS
                // sanitizes on write, but the public site must never render
                // unsanitized HTML regardless of the content's provenance.
                return \App\Libraries\HtmlSanitizer::clean($value);
            }
        }

        return $default;
    }
}

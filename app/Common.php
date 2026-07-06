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
    function lang_url(?string $url = ''): string
    {
        if (empty($url) || $url === '#') {
            return '#';
        }

        // Return absolute URLs as is
        if (preg_match('/^(https?:)?\/\//', $url)) {
            return $url;
        }

        $currentLocale = service('request')->getLocale();
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
     */
    function current_lang_url(string $locale): string
    {
        // If a controller has registered specific localized URLs (e.g. for pages/entries with translated slugs)
        try {
            $renderer = service('renderer');
            $localizedUrls = $renderer->getData()['localized_urls'] ?? null;
            if (is_array($localizedUrls) && isset($localizedUrls[$locale])) {
                $uri = service('request')->getUri();
                $query = $uri->getQuery();
                return $localizedUrls[$locale] . ($query !== '' ? '?' . $query : '');
            }
        } catch (\Throwable) {
            // Fall back if renderer is not loaded or throws
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

if (! function_exists('collection_url_path')) {
    /**
     * Resolve the canonical public path for a collection payload.
     *
     * @param array<string, mixed> $collection
     */
    function collection_url_path(array $collection): string
    {
        $canonicalPath = trim((string) ($collection['slug'] ?? ''), '/');

        return $canonicalPath !== '' ? '/' . $canonicalPath : '';
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
     * Falls back to the collection's current slug when the locale-specific
     * translation is not available in the payload.
     *
     * @param array<string, mixed> $collection
     */
    function localized_collection_url_path(array $collection, string $locale): string
    {
        $localizedSlugs = $collection['localized_slugs'] ?? [];
        if (is_array($localizedSlugs) && isset($localizedSlugs[$locale])) {
            $slug = trim((string) $localizedSlugs[$locale], '/');
            if ($slug !== '') {
                return '/' . $slug;
            }
        }

        return collection_url_path($collection);
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
                // Second-layer sanitization (defense-in-depth): the Domain CMS
                // sanitizes on write, but the public site must never render
                // unsanitized HTML regardless of the content's provenance.
                return \App\Libraries\HtmlSanitizer::clean($value);
            }
        }

        return $default;
    }
}

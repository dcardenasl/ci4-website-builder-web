<?php

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

if (! function_exists('collection_url_prefix')) {
    /**
     * Resolve the canonical public prefix for a collection payload.
     *
     * @param array<string, mixed> $collection
     */
function collection_url_prefix(array $collection): string
    {
        $canonicalPrefix = trim((string) ($collection['slug'] ?? ''), '/');

        return $canonicalPrefix !== '' ? '/' . $canonicalPrefix : '';
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
        $prefix = trim(collection_url_prefix($collection), '/');
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

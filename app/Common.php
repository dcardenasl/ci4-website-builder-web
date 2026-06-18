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

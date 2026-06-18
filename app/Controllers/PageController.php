<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class PageController extends BasePublicWebController
{
    /**
     * Enforce locale prefix in the URL.
     * Returns a redirect response if redirection is needed, or null otherwise.
     */
    protected function enforceLocale(): ?ResponseInterface
    {
        $request = service('request');
        $uri = $request->getUri();
        $firstSegment = $uri->getSegment(1);
        $supportedLocales = config('App')->supportedLocales;

        if (!in_array($firstSegment, $supportedLocales, true)) {
            $locale = $request->getLocale();
            $path = ltrim($uri->getPath(), '/');
            $query = $uri->getQuery();
            $target = '/' . $locale . ($path !== '' ? '/' . $path : '') . ($query !== '' ? '?' . $query : '');
            return redirect()->to($target)->setStatusCode(302);
        }

        return null;
    }

    /**
     * Render the homepage.
     */
    public function home(): ResponseInterface
    {
        if ($redirect = $this->enforceLocale()) {
            return $redirect;
        }

        $lang = service('request')->getLocale();

        // For now, try to fetch a page by slug 'home'
        $pageService = Services::sitePageService();
        $page = $pageService->getBySlug($lang, 'home');

        if (!$page) {
            return $this->notFound('Página de inicio no encontrada');
        }

        return $this->renderPage($page, $lang);
    }

    /**
     * Dynamic page resolver - implements the 5-step resolution algorithm.
     */
    public function resolve(string $path = ''): ResponseInterface
    {
        if ($redirect = $this->enforceLocale()) {
            return $redirect;
        }

        $lang = service('request')->getLocale();
        $path = trim($path, '/');

        if (empty($path)) {
            return $this->home();
        }

        // Step 1: Try CMS page by slug
        $pageService = Services::sitePageService();
        $page = $pageService->getBySlug($lang, $path);

        if ($page) {
            return $this->renderPage($page, $lang);
        }

        // Step 2: Try exact collection url_prefix match
        $collectionService = Services::siteCollectionService();
        $collection = $collectionService->matchByPrefix($lang, '/' . $path);

        if ($collection && ($collection['url_prefix'] ?? '') === '/' . $path) {
            return $this->renderCollectionIndex($collection, $lang);
        }

        // Step 3: Try collection/entry slug combination
        if (strpos($path, '/') !== false) {
            [$prefix, $slug] = explode('/', $path, 2);
            $collection = $collectionService->matchByPrefix($lang, '/' . $prefix);

            if ($collection) {
                $entryService = Services::siteEntryService();
                $entry = $entryService->getBySlug($lang, $collection['collection_key'], $slug);

                if ($entry) {
                    return $this->renderEntry($entry, $collection, $lang);
                }
            }
        }

        // Step 4: Try redirect
        $redirectService = Services::siteRedirectService();
        $redirect = $redirectService->resolve($path);

        if ($redirect) {
            $statusCode = match ($redirect['redirect_type'] ?? 301) {
                'temporary' => 302,
                'permanent' => 301,
                default => 301,
            };

            return redirect($redirect['new_url'])->setStatusCode($statusCode);
        }

        // Step 5: 404
        return $this->notFound("No se encontró la página: {$path}");
    }

    /**
     * Render a CMS page.
     */
    private function renderPage(array $page, string $lang): ResponseInterface
    {
        $blockRenderer = Services::blockRenderer();

        // Get the translation for the current language
        $translation = $this->getPageTranslation($page, $lang);
        $blocks = $page['blocks'] ?? [];
        $hasHeroHeading = false;
        foreach ($blocks as $block) {
            $blockKey = $block['block_key'] ?? '';
            if (in_array($blockKey, ['hero_slider', 'hero_banner', 'page_header'], true)) {
                $hasHeroHeading = true;
                break;
            }
        }

        $data = [
            'title'              => $translation['title'] ?? '',
            'excerpt'            => $translation['excerpt'] ?? '',
            'showPageHeading'    => ! $hasHeroHeading,
            'pageTitle'          => $translation['meta_title'] ?? $translation['title'] ?? '',
            'metaDescription'    => $translation['meta_description'] ?? $translation['excerpt'] ?? '',
            'canonicalUrl'       => $translation['canonical_url'] ?? site_url($translation['slug'] ?? ''),
            'ogImage'            => $translation['og_image_file_id'] ?? '',
            'metaRobots'         => $translation['robots'] ?? 'index, follow',
            'schemaData'         => !empty($translation['schema_data']) ? json_decode($translation['schema_data'], true) : null,
            'renderedBlocks'     => $blockRenderer->render($blocks, $lang),
        ];

        return $this->render('page', $data);
    }

    /**
     * Render a collection index (listing of entries).
     */
    private function renderCollectionIndex(array $collection, string $lang): ResponseInterface
    {
        $entryService = Services::siteEntryService();
        $page = (int) $this->request->getGet('page') ?? 1;
        $limit = 12;

        $result = $entryService->list($lang, $collection['collection_key'], [
            'page'  => $page,
            'limit' => $limit,
        ]);

        $data = [
            'collection'             => $collection,
            'data'                   => $result['data'] ?? [],
            'meta'                   => $result['meta'] ?? [],
            'pageTitle'              => $collection['listing_title'] ?? $collection['name'] ?? '',
            'metaDescription'        => $collection['default_meta_description'] ?? '',
        ];

        return $this->render('collection/index', $data);
    }

    /**
     * Render a collection entry (single item).
     */
    private function renderEntry(array $entry, array $collection, string $lang): ResponseInterface
    {
        $blockRenderer = Services::blockRenderer();

        // Get the translation for the current language
        $translation = $this->getEntryTranslation($entry, $lang);

        $data = [
            'title'                  => $translation['title'] ?? '',
            'excerpt'                => $translation['excerpt'] ?? '',
            'published_at'           => $entry['published_at'] ?? '',
            'categories'             => $entry['categories'] ?? [],
            'tags'                   => $entry['tags'] ?? [],
            'collectionUrlPrefix'    => $collection['url_prefix'] ?? '',
            'pageTitle'              => $translation['meta_title'] ?? $translation['title'] ?? '',
            'metaDescription'        => $translation['meta_description'] ?? $translation['excerpt'] ?? '',
            'canonicalUrl'           => $translation['canonical_url'] ?? site_url($collection['url_prefix'] . '/' . $translation['slug'] ?? ''),
            'ogImage'                => $translation['og_image_file_id'] ?? '',
            'metaRobots'             => $translation['robots'] ?? 'index, follow',
            'schemaData'             => !empty($translation['schema_data']) ? json_decode($translation['schema_data'], true) : null,
            'renderedBlocks'         => $blockRenderer->render($entry['blocks'] ?? [], $lang),
        ];

        return $this->render('collection/show', $data);
    }

    /**
     * Extract translation data from a page based on language.
     *
     * @param array<string, mixed> $page
     * @return array<string, mixed>
     */
    private function getPageTranslation(array $page, string $lang): array
    {
        if (isset($page['title'])) {
            return $page;
        }

        $translations = $page['translations'] ?? [];

        foreach ($translations as $trans) {
            if (($trans['language_id'] ?? null) === $lang || ($trans['language_code'] ?? null) === $lang) {
                return $trans;
            }
        }

        // Fallback to first translation
        return $translations[0] ?? [];
    }

    /**
     * Extract translation data from an entry based on language.
     *
     * @param array<string, mixed> $entry
     * @return array<string, mixed>
     */
    private function getEntryTranslation(array $entry, string $lang): array
    {
        if (isset($entry['title'])) {
            return $entry;
        }

        $translations = $entry['translations'] ?? [];

        foreach ($translations as $trans) {
            if (($trans['language_id'] ?? null) === $lang || ($trans['language_code'] ?? null) === $lang) {
                return $trans;
            }
        }

        // Fallback to first translation
        return $translations[0] ?? [];
    }
}

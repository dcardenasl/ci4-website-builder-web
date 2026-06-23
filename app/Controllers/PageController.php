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
            // Use getSegments() — CI4 already strips index.php from segments
            $segments = $uri->getSegments();
            $path = implode('/', $segments);
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
    public function resolve(string ...$segments): ResponseInterface
    {
        if ($redirect = $this->enforceLocale()) {
            return $redirect;
        }

        $lang = service('request')->getLocale();
        $path = trim(implode('/', $segments), '/');

        if (empty($path)) {
            return $this->home();
        }

        // Step 1: Try CMS page by slug
        $pageService = Services::sitePageService();
        $page = $pageService->getBySlug($lang, $path);

        if ($page) {
            return $this->renderPage($page, $lang);
        }

        // Step 2: Try collection prefix match
        $collectionService = Services::siteCollectionService();
        $entryService = Services::siteEntryService();
        $collections = $collectionService->getAll($lang);

        foreach ($collections as $collection) {
            if (! is_array($collection)) {
                continue;
            }

            $pathInfo = collection_url_path_info($collection, $path);
            if ($pathInfo === null) {
                continue;
            }

            $remainder = $pathInfo['remainder'];

            if ($remainder === '') {
                return $this->renderCollectionIndex($collection, $lang);
            }

            $entry = $entryService->getBySlug($lang, $collection['collection_key'], $remainder);

            if ($entry) {
                return $this->renderEntry($entry, $collection, $lang);
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

        $localizedUrls = [];
        foreach (($page['localized_slugs'] ?? []) as $loc => $slug) {
            if ($slug !== null) {
                $slugPath = trim($slug, '/');
                if ($slugPath === 'home' || $slugPath === '') {
                    $localizedUrls[$loc] = site_url('/' . $loc);
                } else {
                    $localizedUrls[$loc] = site_url('/' . $loc . '/' . ltrim($slugPath, '/'));
                }
            }
        }

        $data = [
            'title'              => $translation['title'] ?? '',
            'excerpt'            => $translation['excerpt'] ?? '',
            'showPageHeading'    => ! $hasHeroHeading,
            'pageTitle'          => $translation['meta_title'] ?? $translation['title'] ?? '',
            'metaDescription'    => $translation['meta_description'] ?? $translation['excerpt'] ?? '',
            'canonicalUrl'       => $translation['canonical_url'] ?: site_url('/' . $lang . '/' . ltrim($translation['slug'] ?? '', '/')),
            'ogImage'            => $translation['og_image_file_id'] ?? '',
            'metaRobots'         => $translation['robots'] ?? 'index, follow',
            'schemaData'         => !empty($translation['schema_data']) ? json_decode($translation['schema_data'], true) : null,
            'renderedBlocks'     => $blockRenderer->render($blocks, $lang),
            'localized_urls'     => $localizedUrls,
        ];

        return $this->render('page', $data);
    }

    /**
     * Render a collection index (listing of entries).
     */
    private function renderCollectionIndex(array $collection, string $lang): ResponseInterface
    {
        $entryService    = Services::siteEntryService();
        $categoryService = Services::siteCategoryService();
        $collectionUrlPath = collection_url_path($collection);

        $currentPage     = max(1, (int) ($this->request->getGet('page') ?? 1));
        $currentCategory = (string) ($this->request->getGet('category') ?? '');

        $query = [
            'page'     => $currentPage,
            'per_page' => 12,
        ];
        if ($currentCategory !== '') {
            $query['category'] = $currentCategory;
        }

        $result     = $entryService->list($lang, $collection['collection_key'], $query);
        $categories = $categoryService->list($lang, $collection['collection_key']);

        $data = [
            'collection'      => $collection,
            'data'            => $result['data'] ?? [],
            'meta'            => $result['meta'] ?? [],
            'categories'      => $categories,
            'currentCategory' => $currentCategory,
            'currentPage'     => $currentPage,
            'pagination'      => $result['meta']['pagination'] ?? [],
            'pageTitle'       => $collection['listing_title'] ?? $collection['name'] ?? '',
            'metaDescription' => $collection['default_meta_description'] ?? '',
            'lang'            => $lang,
            'collectionUrlPath'   => $collectionUrlPath,
        ];

        return $this->render('collection/index', $data);
    }

    /**
     * Render a collection entry (single item).
     */
    private function renderEntry(array $entry, array $collection, string $lang): ResponseInterface
    {
        $blockRenderer = Services::blockRenderer();
        $entryService  = Services::siteEntryService();

        // Get the translation for the current language
        $translation = $this->getEntryTranslation($entry, $lang);

        // Fetch recent posts from the same collection (exclude current entry)
        $recentResult = $entryService->list($lang, $collection['collection_key'], [
            'per_page' => 4,
            'page'     => 1,
        ]);
        $currentSlug  = $translation['slug'] ?? '';
        $recentPosts  = array_values(array_filter(
            $recentResult['data'] ?? [],
            static fn (array $e): bool => ($e['slug'] ?? '') !== $currentSlug
        ));
        $recentPosts  = array_slice($recentPosts, 0, 3);

        $localizedUrls = [];
        $collectionUrlPath = collection_url_path($collection);
        foreach (($entry['localized_slugs'] ?? []) as $loc => $slug) {
            if ($slug !== null) {
                $localizedUrls[$loc] = site_url('/' . $loc . $collectionUrlPath . '/' . ltrim($slug, '/'));
            }
        }

        $data = [
            'title'               => $translation['title'] ?? '',
            'excerpt'             => $translation['excerpt'] ?? '',
            'published_at'        => $entry['published_at'] ?? '',
            'featured_image_url'  => $entry['featured_image_url'] ?? '',
            'author_id'           => $entry['author_id'] ?? null,
            'categories'          => $entry['categories'] ?? [],
            'tags'                => $entry['tags'] ?? [],
            'collectionName'      => $collection['listing_title'] ?? $collection['name'] ?? '',
            'collectionUrlPath'   => $collectionUrlPath,
            'recentPosts'         => $recentPosts,
            'lang'                => $lang,
            'pageTitle'           => $translation['meta_title'] ?? $translation['title'] ?? '',
            'metaDescription'     => $translation['meta_description'] ?? $translation['excerpt'] ?? '',
            'canonicalUrl'        => $translation['canonical_url'] ?: site_url('/' . $lang . $collectionUrlPath . '/' . ltrim($translation['slug'] ?? '', '/')),
            'ogImage'             => $translation['og_image_file_id'] ?? '',
            'metaRobots'          => $translation['robots'] ?? 'index, follow',
            'schemaData'          => !empty($translation['schema_data']) ? json_decode($translation['schema_data'], true) : null,
            'renderedBlocks'      => $blockRenderer->render($entry['blocks'] ?? [], $lang),
            'localized_urls'      => $localizedUrls,
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

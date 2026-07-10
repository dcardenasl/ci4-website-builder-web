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
     * Reads the preview query params off the incoming request and forwards
     * them opaquely — this app never validates the signature itself, only
     * Domain does (PreviewToken::verify). Passing an invalid or missing
     * signature through just means Domain falls back to published-only rules.
     *
     * @return array{0: bool, 1: ?string, 2: ?string}
     */
    private function previewParams(): array
    {
        $request = service('request');
        $preview = $request->getGet('preview') === '1';
        $previewExpires = $request->getGet('preview_expires');
        $previewSig = $request->getGet('preview_sig');

        return [
            $preview,
            is_string($previewExpires) ? $previewExpires : null,
            is_string($previewSig) ? $previewSig : null,
        ];
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
        [$preview, $previewExpires, $previewSig] = $this->previewParams();

        // For now, try to fetch a page by slug 'home'
        $pageService = Services::sitePageService();
        $page = $pageService->getBySlug($lang, 'home', $preview, $previewExpires, $previewSig);

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
        [$preview, $previewExpires, $previewSig] = $this->previewParams();

        if (empty($path)) {
            return $this->home();
        }

        // Step 1: Try collection prefix match first.
        $collectionService = Services::siteCollectionService();
        $entryService = Services::siteEntryService();
        $pageService = Services::sitePageService();
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
                $page = $pageService->getBySlug($lang, $path, $preview, $previewExpires, $previewSig);
                if ($page && (string) ($page['page_type'] ?? '') === 'collection_index' && (int) ($page['collection_id'] ?? 0) === (int) ($collection['id'] ?? 0)) {
                    return $this->renderPage($page, $lang);
                }

                return $this->notFound("No se encontró la página índice para la colección: {$path}");
            }

            $entry = $entryService->getBySlug($lang, $collection['collection_key'], $remainder, $preview, $previewExpires, $previewSig);

            if ($entry) {
                return $this->renderEntry($entry, $collection, $lang);
            }
        }

        // Step 2: Try CMS page by slug only when the path is not a collection route.
        $page = $pageService->getBySlug($lang, $path, $preview, $previewExpires, $previewSig);

        if ($page) {
            return $this->renderPage($page, $lang);
        }

        // Step 3: Try redirect
        $redirectService = Services::siteRedirectService();
        $redirect = $redirectService->resolve($path);

        if ($redirect) {
            $statusCode = match ($redirect['redirect_type'] ?? 301) {
                'temporary' => 302,
                'permanent' => 301,
                default => 301,
            };

            return redirect()->to((string) $redirect['new_url'])->setStatusCode($statusCode);
        }

        // Step 4: 404
        return $this->notFound("No se encontró la página: {$path}");
    }

    /**
     * Render a CMS page.
     *
     * @param array<string, mixed> $page
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
            'canonicalUrl'       => ($translation['canonical_url'] ?? '') !== ''
                ? $translation['canonical_url']
                : site_url('/' . $lang . '/' . ltrim((string) ($translation['slug'] ?? ''), '/')),
            'ogImage'            => $translation['og_image_url'] ?? '',
            'metaRobots'         => $translation['robots'] ?? 'index, follow',
            'schemaData'         => !empty($translation['schema_data']) ? json_decode($translation['schema_data'], true) : null,
            'renderedBlocks'     => $blockRenderer->render($blocks, $lang),
            'localized_urls'     => $localizedUrls,
        ];

        return $this->render('page', $data);
    }

    /**
     * Render a collection entry (single item).
     *
     * @param array<string, mixed> $entry
     * @param array<string, mixed> $collection
     */
    private function renderEntry(array $entry, array $collection, string $lang): ResponseInterface
    {
        $blockRenderer = Services::blockRenderer();

        // Get the translation for the current language
        $translation = $this->getEntryTranslation($entry, $lang);

        $collectionUrlPath = collection_url_path($collection);
        $canonicalUrl = ($translation['canonical_url'] ?? '') !== ''
            ? $translation['canonical_url']
            : site_url('/' . $lang . $collectionUrlPath . '/' . ltrim((string) ($translation['slug'] ?? ''), '/'));

        $allowedOgTypes = ['article', 'website'];
        $ogType = in_array($translation['og_type'] ?? '', $allowedOgTypes, true) ? $translation['og_type'] : 'article';

        // The API serializes CodeIgniter Time fields (e.g. updated_at) as
        // {date, timezone_type, timezone} rather than a plain string.
        $updatedAtRaw = $entry['updated_at'] ?? null;
        $articleModifiedTime = is_array($updatedAtRaw) ? ($updatedAtRaw['date'] ?? null) : $updatedAtRaw;

        $relatedEntries = [];
        try {
            $relatedEntries = Services::siteEntryService()->related(
                $lang,
                $collection['collection_key'],
                ['slug' => $translation['slug'] ?? '', 'categories' => $entry['categories'] ?? []],
                3
            );
        } catch (\Throwable) {
            $relatedEntries = [];
        }

        // Entries whose own blocks already render a heading/hero image must not
        // duplicate the article template's hardcoded title/featured image.
        $hasHeroHeading = false;
        $hasHeroImage = false;
        foreach (($entry['blocks'] ?? []) as $block) {
            $blockKey = $block['block_key'] ?? '';
            if (in_array($blockKey, ['hero_slider', 'hero_banner', 'page_header'], true)) {
                $hasHeroHeading = true;
            }
            if (in_array($blockKey, ['hero_slider', 'hero_banner'], true)) {
                $hasHeroImage = true;
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
            'relatedEntries'      => $relatedEntries,
            'showEntryHeading'    => ! $hasHeroHeading,
            'showFeaturedImage'   => ! $hasHeroImage,
            'lang'                => $lang,
            'pageTitle'           => $translation['meta_title'] ?? $translation['title'] ?? '',
            'metaDescription'     => $translation['meta_description'] ?? $translation['excerpt'] ?? '',
            'canonicalUrl'        => $canonicalUrl,
            'ogImage'             => $translation['og_image_url'] ?? ($entry['featured_image_url'] ?? ''),
            'ogType'              => $ogType,
            'articlePublishedTime' => $entry['published_at'] ?? null,
            'articleModifiedTime'  => $articleModifiedTime,
            'metaRobots'          => $translation['robots'] ?? 'index, follow',
            'schemaData'          => !empty($translation['schema_data']) ? json_decode($translation['schema_data'], true) : null,
            'renderedBlocks'      => $blockRenderer->render($entry['blocks'] ?? [], $lang),
            'localized_urls'      => localized_entry_urls($collection, $entry),
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
            $translation = $page;
            if (! isset($translation['og_image_url']) && isset($page['translations']) && is_array($page['translations'])) {
                foreach ($page['translations'] as $trans) {
                    if (($trans['language_id'] ?? null) === $lang || ($trans['language_code'] ?? null) === $lang) {
                        $translation = array_merge($translation, $trans);
                        break;
                    }
                }
            }

            return $translation;
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
            $translation = $entry;
            if ((! isset($translation['og_image_url']) || ! isset($translation['featured_image_url'])) && isset($entry['translations']) && is_array($entry['translations'])) {
                foreach ($entry['translations'] as $trans) {
                    if (($trans['language_id'] ?? null) === $lang || ($trans['language_code'] ?? null) === $lang) {
                        $translation = array_merge($translation, $trans);
                        break;
                    }
                }
            }

            return $translation;
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

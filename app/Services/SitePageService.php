<?php

declare(strict_types=1);

namespace App\Services;

class SitePageService extends BaseSiteService
{
    private const CACHE_TTL_DETAIL = 300; // 5 minutes for single page
    private const CACHE_TTL_LIST = 600;   // 10 minutes for list

    /**
     * Get a published page by slug.
     *
     * @return array<string, mixed>|null
     */
    public function getBySlug(string $lang, string $slug): ?array
    {
        return $this->fetchData("public/{$lang}/pages/{$slug}", [], self::CACHE_TTL_DETAIL, 'pages');
    }

    /**
     * List all published pages for a language (for sitemap generation).
     *
     * @return array<array<string, mixed>>
     */
    public function listAll(string $lang): array
    {
        return $this->fetchData("public/{$lang}/pages", [], self::CACHE_TTL_LIST, 'pages') ?? [];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Public cold-page composition adapter.
 *
 * The Domain endpoint returns layout data and the resolved route together so
 * a page miss does not fan out into independent menu, collection and content
 * requests. Existing resource services remain the fallback seam.
 */
final class SiteBootstrapService extends BaseSiteService
{
    private const LAYOUT_TTL = 600;
    private const PAGE_TTL = 300;

    /** @return array{settings: array<string, mixed>, menus: array<string, mixed>, lang?: string|null} */
    public function getLayout(): array
    {
        $data = $this->fetchData('public/layout', [], self::LAYOUT_TTL, 'layout');

        if (! is_array($data)) {
            return ['settings' => [], 'menus' => []];
        }

        return [
            'settings' => is_array($data['settings'] ?? null) ? $data['settings'] : [],
            'menus'    => is_array($data['menus'] ?? null) ? $data['menus'] : [],
            'lang'     => isset($data['lang']) ? (string) $data['lang'] : null,
        ];
    }

    /** @return array{layout: array<string, mixed>, route: array<string, mixed>}|null */
    public function getPageBootstrap(
        string $path,
        bool $preview = false,
        ?string $previewExpires = null,
        ?string $previewSig = null,
    ): ?array {
        $query = [];
        if ($preview) {
            $query['preview'] = '1';
            if ($previewExpires !== null) {
                $query['preview_expires'] = $previewExpires;
            }
            if ($previewSig !== null) {
                $query['preview_sig'] = $previewSig;
            }
        }

        $data = $this->fetchData(
            'public/page-bootstrap/' . trim($path, '/'),
            $query,
            $preview ? 0 : self::PAGE_TTL,
            'bootstrap'
        );

        if (! is_array($data) || ! is_array($data['route'] ?? null) || ! is_array($data['layout'] ?? null)) {
            return null;
        }

        return [
            'layout' => $data['layout'],
            'route'  => $data['route'],
        ];
    }
}

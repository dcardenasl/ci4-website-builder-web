<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

abstract class BasePublicWebController extends BaseController
{
    /** @param array<string,mixed> $data */
    protected function render(string $view, array $data = []): ResponseInterface
    {
        $data['view'] = $view;

        if (empty($data['canonicalUrl'])) {
            $data['canonicalUrl'] = site_url($this->request->getPath());
        }

        // A cold page can bring its layout in the same response as the route.
        // Keep the individual service calls as a graceful fallback for older
        // Domain deployments and isolated tests.
        $composedLayout = is_array($data['_layout'] ?? null) ? $data['_layout'] : null;
        unset($data['_layout']);

        if ($composedLayout !== null) {
            $menus = is_array($composedLayout['menus'] ?? null) ? $composedLayout['menus'] : [];
            $data['mainMenu'] = is_array($menus['main'] ?? null) ? $menus['main'] : ['items' => []];
            $data['footerMenu'] = is_array($menus['footer'] ?? null) ? $menus['footer'] : ['items' => []];
            $data['legalMenu'] = is_array($menus['legal'] ?? null) ? $menus['legal'] : ['items' => []];
            $data['settings'] = is_array($composedLayout['settings'] ?? null) ? $composedLayout['settings'] : [];
            $data['socialLinks'] = \Config\Services::socialLinksService()->getActiveLinksFromSettings($data['settings']);
        } else {
            if (! isset($data['mainMenu'])) {
                try {
                    $data['mainMenu'] = \Config\Services::siteMenuService()->getMenu('main');
                } catch (\Throwable) {
                    $data['mainMenu'] = ['items' => []];
                }
            }

            if (! isset($data['footerMenu'])) {
                try {
                    $data['footerMenu'] = \Config\Services::siteMenuService()->getMenu('footer');
                } catch (\Throwable) {
                    $data['footerMenu'] = ['items' => []];
                }
            }

            if (! isset($data['legalMenu'])) {
                try {
                    $data['legalMenu'] = \Config\Services::siteMenuService()->getMenu('legal');
                } catch (\Throwable) {
                    $data['legalMenu'] = ['items' => []];
                }
            }

            if (! isset($data['settings'])) {
                try {
                    $data['settings'] = \Config\Services::siteSettingsService()->getAll();
                } catch (\Throwable) {
                    $data['settings'] = [];
                }
            }
        }

        if (! array_key_exists('schemaData', $data)) {
            $data['schemaData'] = null;
        }

        // layouts/public.php forwards the full page data to nested partials
        // (head, $view) as a single $data variable, so it needs it under its
        // own 'data' key explicitly — it must not rely on Config\View's
        // saveData persistence to leak it in as a side effect.
        $data['data'] = $data;

        // saveData:false — Config\View::$saveData defaults to true and would
        // otherwise persist this render's data into the shared view store for
        // the rest of the process (e.g. across PHPUnit test cases).
        $body = view('layouts/public', $data, ['saveData' => false]);
        $etag = '"' . sha1($body) . '"';

        $isSnapshotMode = config('App')->pageDeliveryMode === 'snapshot';
        $cacheControl = $isSnapshotMode
            ? 'public, max-age=900, stale-while-revalidate=300'
            : 'public, max-age=300, stale-while-revalidate=60';

        // Activate CI4's own page-cache store to match the Cache-Control
        // header above — without this, `cache:warmup` visits pages that are
        // never actually cached, and CsrfCookieFilter's post-pagecache
        // ordering guards a caching mode that never engages. GET only: a
        // cached response must never be a replay of a form submission.
        if ($isSnapshotMode && $this->request->is('get')) {
            $this->cachePage(900);
        }

        return $this->response
            ->setHeader('Cache-Control', $cacheControl)
            ->setHeader('ETag', $etag)
            ->setHeader('Vary', 'Accept-Language')
            ->setBody($body);
    }

    protected function notFound(string $message = 'Página no encontrada'): ResponseInterface
    {
        return $this->render('errors/404', ['message' => $message])
            ->setStatusCode(404);
    }
}

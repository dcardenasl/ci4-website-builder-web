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

        if (! isset($data['canonicalUrl'])) {
            $data['canonicalUrl'] = site_url($this->request->getPath());
        }

        // Pre-load global layout data: menus and settings
        if (! isset($data['mainMenu'])) {
            try {
                $data['mainMenu'] = \Config\Services::siteMenuService()->getMenu('main-nav');
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

        if (! isset($data['settings'])) {
            try {
                $data['settings'] = \Config\Services::siteSettingsService()->getAll();
            } catch (\Throwable) {
                $data['settings'] = [];
            }
        }

        return $this->response->setBody(
            view('layouts/public', $data)
        );
    }

    protected function notFound(string $message = 'Página no encontrada'): ResponseInterface
    {
        return $this->render('errors/404', ['message' => $message])
            ->setStatusCode(404);
    }
}

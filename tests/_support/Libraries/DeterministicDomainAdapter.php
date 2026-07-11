<?php

declare(strict_types=1);

namespace Tests\Support\Libraries;

use App\Libraries\WebApiClientInterface;

/**
 * In-memory Domain adapter for hermetic Web feature tests.
 */
final class DeterministicDomainAdapter implements WebApiClientInterface
{
    /**
     * @var array<string, array{ok: bool, status: int, data: mixed, meta: array<string, mixed>, messages: list<string>}>
     */
    private array $responses = [];

    /**
     * @param mixed                $data
     * @param array<string, mixed> $meta
     */
    public function fakeGet(string $path, mixed $data, array $meta = []): void
    {
        $this->responses[$path] = $this->response($data, $meta);
    }

    public function fakeGetFailure(string $path, int $status = 404): void
    {
        $this->responses[$path] = [
            'ok'       => false,
            'status'   => $status,
            'data'     => null,
            'meta'     => [],
            'messages' => ['Not found'],
        ];
    }

    public function get(string $path, array $query = [], int $cacheTtl = 300, string $scope = 'general'): array
    {
        unset($query, $cacheTtl, $scope);

        if (isset($this->responses[$path])) {
            return $this->responses[$path];
        }

        if ($path === 'public/settings') {
            return $this->response([
                'site_name'        => 'Deterministic Test Site',
                'site_description' => 'A deterministic public website fixture for hermetic feature tests.',
                'site_logo_url'    => 'https://example.com/assets/test-logo.png',
            ]);
        }

        if (str_starts_with($path, 'public/menus/')) {
            return $this->response(['items' => []]);
        }

        if (preg_match('#^public/(es|en)/pages/home$#', $path, $matches) === 1) {
            return $this->response($this->homePage($matches[1]));
        }

        if (preg_match('#^public/(es|en)/pages$#', $path, $matches) === 1) {
            return $this->response([$this->homePage($matches[1])]);
        }

        if (preg_match('#^public/(es|en)/collections$#', $path) === 1) {
            return $this->response([]);
        }

        return [
            'ok'       => false,
            'status'   => 404,
            'data'     => null,
            'meta'     => [],
            'messages' => ['Not found'],
        ];
    }

    public function post(string $path, array $data = []): array
    {
        unset($path, $data);

        return $this->response([]);
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return array{ok: bool, status: int, data: mixed, meta: array<string, mixed>, messages: list<string>}
     */
    private function response(mixed $data, array $meta = []): array
    {
        return [
            'ok'       => true,
            'status'   => 200,
            'data'     => $data,
            'meta'     => $meta,
            'messages' => [],
        ];
    }

    /** @return array<string, mixed> */
    private function homePage(string $locale): array
    {
        $isSpanish = $locale === 'es';

        return [
            'title'            => $isSpanish ? 'Inicio de prueba' : 'Test homepage',
            'slug'             => 'home',
            'excerpt'          => $isSpanish
                ? 'Contenido estable para validar el marcado público sin depender de Domain.'
                : 'Stable content used to validate public markup without depending on Domain.',
            'meta_title'       => $isSpanish ? 'Sitio de prueba determinista' : 'Deterministic test site',
            'meta_description' => $isSpanish
                ? 'Página de inicio determinista para validar metadatos y HTML en pruebas herméticas.'
                : 'A deterministic homepage used to validate metadata and HTML in hermetic tests.',
            'canonical_url'    => '',
            'robots'           => 'index, follow',
            'is_in_sitemap'    => true,
            'updated_at'       => '2026-01-01T00:00:00+00:00',
            'sitemap_changefreq' => 'weekly',
            'sitemap_priority' => '1.0',
            'blocks'           => [],
            'localized_slugs'  => ['es' => 'home', 'en' => 'home'],
        ];
    }
}

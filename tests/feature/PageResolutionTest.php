<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\WebApiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * Feature tests for PageController's dynamic resolver.
 *
 * @internal
 */
final class PageResolutionTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private FakeWebApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);
        $this->client = new FakeWebApiClient();
        Services::injectMock('webApiClient', $this->client);
    }

    protected function tearDown(): void
    {
        Services::reset(true);

        parent::tearDown();
    }

    public function testResolvesCmsPage(): void
    {
        $this->client->fakeGet('public/es/collections', []);
        $this->client->fakeGet('public/es/pages/nosotros', [
            'title'              => 'Nosotros',
            'slug'               => 'nosotros',
            'excerpt'            => 'Pagina institucional',
            'meta_description'   => 'Meta nosotros',
            'canonical_url'      => '',
            'blocks'             => [],
            'localized_slugs'    => ['es' => 'nosotros'],
        ]);

        $result = $this->get('es/nosotros');

        $result->assertStatus(200);
        $result->assertSee('Nosotros');
    }

    public function testResolvesCollectionPrefixAsIndexBeforeCmsPage(): void
    {
        $this->client->fakeGet('public/es/collections', [$this->collection()]);
        $this->client->fakeGet('public/es/entries/news', [], ['total_pages' => 1]);
        $this->client->fakeGet('public/es/categories/news', []);
        $this->client->fakeGet('public/es/pages/noticias', [
            'title'          => 'Noticias',
            'slug'           => 'noticias',
            'page_type'      => 'collection_index',
            'collection_id'  => 1,
            'canonical_url'  => '',
            'localized_slugs' => ['es' => 'noticias'],
        ]);

        $result = $this->get('es/noticias');

        $result->assertStatus(200);
        $result->assertSee('Noticias');
        $result->assertDontSee('CMS page that should not win');
    }

    public function testResolvesCollectionEntry(): void
    {
        $this->client->fakeGet('public/es/collections', [$this->collection()]);
        $this->client->fakeGet('public/es/entries/news/primer-post', [
            'title'            => 'Primer post',
            'slug'             => 'primer-post',
            'excerpt'          => 'Entrada publicada',
            'meta_description' => 'Meta entry',
            'canonical_url'    => '',
            'published_at'     => '2026-07-06 12:00:00',
            'blocks'           => [],
            'localized_slugs'  => ['es' => 'primer-post'],
        ]);

        $result = $this->get('es/noticias/primer-post');

        $result->assertStatus(200);
        $result->assertSee('Primer post');
        $result->assertSee('Noticias');
    }

    public function testResolvesPermanentRedirect(): void
    {
        $this->client->fakeGet('public/es/collections', []);
        $this->client->fakeGetFailure('public/es/pages/vieja');
        $this->client->fakeGet('public/redirects/vieja', [
            'new_url'       => '/es/nueva',
            'redirect_type' => 'permanent',
        ]);

        $result = $this->get('es/vieja');

        $result->assertStatus(301);
        $result->assertHeader('Location', site_url('/es/nueva'));
    }

    public function testReturns404WhenNothingMatches(): void
    {
        $this->client->fakeGet('public/es/collections', []);
        $this->client->fakeGetFailure('public/es/pages/no-existe');
        $this->client->fakeGetFailure('public/redirects/no-existe');

        $result = $this->get('es/no-existe');

        $result->assertStatus(404);
        $result->assertSee('no-existe');
    }

    /**
     * @return array<string, mixed>
     */
    private function collection(): array
    {
        return [
            'id'                       => 1,
            'collection_key'           => 'news',
            'slug'                     => 'noticias',
            'name'                     => 'Noticias',
            'listing_title'            => 'Noticias',
            'listing_intro'            => '',
            'default_meta_description' => 'Ultimas noticias',
            'index_page'               => [
                'localized_slugs' => [
                    'es' => 'noticias',
                    'en' => 'news',
                ],
            ],
        ];
    }
}

final class FakeWebApiClient implements WebApiClientInterface
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
        $this->responses[$path] = [
            'ok'       => true,
            'status'   => 200,
            'data'     => $data,
            'meta'     => $meta,
            'messages' => [],
        ];
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

    /**
     * @param array<string, mixed> $query
     *
     * @return array{ok: bool, status: int, data: mixed, meta: array<string, mixed>, messages: list<string>}
     */
    public function get(string $path, array $query = [], int $cacheTtl = 300, string $scope = 'general'): array
    {
        unset($query, $cacheTtl, $scope);

        if (isset($this->responses[$path])) {
            return $this->responses[$path];
        }

        if ($path === 'public/settings') {
            return ['ok' => true, 'status' => 200, 'data' => [], 'meta' => [], 'messages' => []];
        }

        if (str_starts_with($path, 'public/menus/')) {
            return ['ok' => true, 'status' => 200, 'data' => ['items' => []], 'meta' => [], 'messages' => []];
        }

        return ['ok' => false, 'status' => 404, 'data' => null, 'meta' => [], 'messages' => ['Not found']];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{ok: bool, status: int, data: mixed, meta: array<string, mixed>, messages: list<string>}
     */
    public function post(string $path, array $data = []): array
    {
        unset($path, $data);

        return ['ok' => true, 'status' => 200, 'data' => [], 'meta' => [], 'messages' => []];
    }
}

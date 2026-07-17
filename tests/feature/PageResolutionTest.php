<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\HermeticFeatureTestCase;

/**
 * Feature tests for PageController's dynamic resolver.
 *
 * @internal
 */
final class PageResolutionTest extends HermeticFeatureTestCase
{
    use FeatureTestTrait;

    public function testResolvesCmsPage(): void
    {
        $this->domainAdapter->fakeGet('public/es/collections', []);
        $this->domainAdapter->fakeGet('public/es/pages/nosotros', [
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

    public function testResolvesLocalizedLegalDataRightsPageInSpanish(): void
    {
        $this->domainAdapter->fakeGet('public/es/collections', []);
        $this->domainAdapter->fakeGet('public/es/pages/derechos-datos', [
            'title'           => 'Derechos de Datos',
            'slug'            => 'derechos-datos',
            'excerpt'         => 'Formulario y preguntas frecuentes para ejercer sus derechos ARCO/RGPD.',
            'meta_description'=> 'Ejercite sus derechos de Acceso, Rectificación, Supresión u Oposición sobre sus datos.',
            'canonical_url'   => '',
            'blocks'          => [],
            'localized_slugs' => [
                'es' => 'derechos-datos',
                'en' => 'data-rights',
            ],
        ]);

        $result = $this->get('es/derechos-datos');

        $result->assertStatus(200);
        $result->assertSee('Derechos de Datos');
    }

    public function testResolvesLocalizedLegalDataRightsPageInEnglish(): void
    {
        $this->domainAdapter->fakeGet('public/en/collections', []);
        $this->domainAdapter->fakeGet('public/en/pages/data-rights', [
            'title'           => 'Data Rights',
            'slug'            => 'data-rights',
            'excerpt'         => 'Form and FAQs to exercise your GDPR rights over your personal data.',
            'meta_description'=> 'Exercise your rights of Access, Rectification, Erasure, or Objection.',
            'canonical_url'   => '',
            'blocks'          => [],
            'localized_slugs' => [
                'es' => 'derechos-datos',
                'en' => 'data-rights',
            ],
        ]);

        $result = $this->get('en/data-rights');

        $result->assertStatus(200);
        $result->assertSee('Data Rights');
    }

    public function testResolvesCollectionPrefixAsIndexBeforeCmsPage(): void
    {
        $this->domainAdapter->fakeGet('public/es/collections', [$this->collection()]);
        $this->domainAdapter->fakeGet('public/es/entries/news', [], ['total_pages' => 1]);
        $this->domainAdapter->fakeGet('public/es/categories/news', []);
        $this->domainAdapter->fakeGet('public/es/pages/noticias', [
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
        $this->domainAdapter->fakeGet('public/es/collections', [$this->collection()]);
        $this->domainAdapter->fakeGet('public/es/entries/news/primer-post', [
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

    public function testResolvesCollectionEntryWithEmptyListingTitleFallsBackToName(): void
    {
        $this->domainAdapter->fakeGet('public/es/collections', [[
            'id'                       => 4,
            'collection_key'           => 'festivales',
            'slug'                     => 'festivales',
            'name'                     => 'Festivales',
            'listing_title'            => '',
            'listing_intro'            => '',
            'default_meta_description' => 'Festivales de teatro',
            'index_page'               => [
                'localized_slugs' => [
                    'es' => 'festivales',
                    'en' => 'festivals',
                ],
            ],
        ]]);
        $this->domainAdapter->fakeGet('public/es/entries/festivales/primer-post', [
            'title'            => 'Primer post',
            'slug'             => 'primer-post',
            'excerpt'          => 'Entrada publicada',
            'meta_description' => 'Meta entry',
            'canonical_url'    => '',
            'published_at'     => '2026-07-06 12:00:00',
            'blocks'           => [],
            'localized_slugs'  => ['es' => 'primer-post'],
        ]);

        $result = $this->get('es/festivales/primer-post');

        $result->assertStatus(200);
        $result->assertSee('Primer post');
        $result->assertSee('Festivales');
    }

    public function testResolvesEntryFromCmsPageWithCollectionListingBlock(): void
    {
        $this->domainAdapter->fakeGet('public/es/collections', [$this->collection()]);
        $this->domainAdapter->fakeGet('public/es/pages/festivales', [
            'title'           => 'Festivales',
            'slug'            => 'festivales',
            'excerpt'         => 'Pagina de Festivales',
            'meta_description' => 'Meta festivales',
            'canonical_url'   => '',
            'blocks'          => [
                [
                    'block_key' => 'collection_listing',
                    'block_config' => [
                        'collection_id' => 1,
                    ],
                    'children' => [],
                ],
            ],
            'localized_slugs' => ['es' => 'festivales'],
        ]);
        $this->domainAdapter->fakeGet('public/es/entries/news/primer-post', [
            'title'            => 'Primer post',
            'slug'             => 'primer-post',
            'excerpt'          => 'Entrada publicada',
            'meta_description' => 'Meta entry',
            'canonical_url'    => '',
            'published_at'     => '2026-07-06 12:00:00',
            'blocks'           => [],
            'localized_slugs'  => ['es' => 'primer-post'],
        ]);

        $result = $this->get('es/festivales/primer-post');

        $result->assertStatus(200);
        $result->assertSee('Primer post');
        $result->assertSee('Volver a Noticias');
    }

    public function testResolvesPermanentRedirect(): void
    {
        $this->domainAdapter->fakeGet('public/es/collections', []);
        $this->domainAdapter->fakeGetFailure('public/es/pages/vieja');
        $this->domainAdapter->fakeGet('public/redirects/vieja', [
            'new_url'       => '/es/nueva',
            'redirect_type' => 'permanent',
        ]);

        $result = $this->get('es/vieja');

        $result->assertStatus(301);
        $result->assertHeader('Location', site_url('/es/nueva'));
    }

    public function testReturns404WhenNothingMatches(): void
    {
        $this->domainAdapter->fakeGet('public/es/collections', []);
        $this->domainAdapter->fakeGetFailure('public/es/pages/no-existe');
        $this->domainAdapter->fakeGetFailure('public/redirects/no-existe');

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

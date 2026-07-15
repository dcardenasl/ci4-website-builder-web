<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CollectionUrlHelpersTest extends CIUnitTestCase
{
    public function testCanonicalPathIsNormalized(): void
    {
        service('request')->setLocale('en');

        $collection = [
            'index_page' => [
                'localized_slugs' => [
                    'en' => '/news',
                ],
            ],
        ];

        $this->assertSame('/news', collection_url_path($collection));
    }

    public function testCanonicalPathMatchesAndReportsPrefix(): void
    {
        service('request')->setLocale('en');

        $collection = [
            'index_page' => [
                'localized_slugs' => [
                    'en' => 'news',
                ],
            ],
        ];

        $info = collection_url_path_info($collection, 'news/welcome-to-our-news');

        $this->assertNotNull($info);
        $this->assertSame('/news', $info['prefix']);
        $this->assertSame('welcome-to-our-news', $info['remainder']);
    }

    public function testCanonicalPathFallsBackToCollectionKeyWithoutIndexPage(): void
    {
        service('request')->setLocale('en');

        // Without a dedicated index page, entry links must still resolve to a
        // stable, collection-derived prefix — not depend on whichever page
        // happens to embed the listing block (see PageController::resolve()
        // Step 1, which relies on this prefix being routable).
        $collection = [
            'collection_key' => 'noticias',
        ];

        $this->assertSame('/noticias', collection_url_path($collection));

        $info = collection_url_path_info($collection, 'noticias/bienvenidos-a-nuestras-noticias');
        $this->assertNotNull($info);
        $this->assertSame('/noticias', $info['prefix']);
        $this->assertSame('bienvenidos-a-nuestras-noticias', $info['remainder']);
    }

    public function testCanonicalPathReturnsEmptyWithoutSlugOrCollectionKey(): void
    {
        service('request')->setLocale('en');

        $collection = [];

        $this->assertSame('', collection_url_path($collection));
        $this->assertNull(collection_url_path_info($collection, 'anything'));
    }

    public function testLocalizedCollectionUrlPathUsesTranslatedSlug(): void
    {
        service('request')->setLocale('en');

        $collection = [
            'index_page' => [
                'localized_slugs' => [
                    'es' => 'noticias',
                    'en' => 'news',
                ],
            ],
        ];

        $this->assertSame('/noticias', localized_collection_url_path($collection, 'es'));
        $this->assertSame('/news', localized_collection_url_path($collection, 'en'));
    }

    public function testLocalizedEntryUrlsUseTranslatedCollectionAndEntrySlugs(): void
    {
        service('request')->setLocale('en');

        $collection = [
            'index_page' => [
                'localized_slugs' => [
                    'es' => 'noticias',
                    'en' => 'news',
                ],
            ],
        ];

        $entry = [
            'localized_slugs' => [
                'es' => 'bienvenidos-a-nuestras-noticias',
                'en' => 'welcome-to-our-news',
            ],
        ];

        $esPath = parse_url(localized_entry_urls($collection, $entry)['es'], PHP_URL_PATH);
        $enPath = parse_url(localized_entry_urls($collection, $entry)['en'], PHP_URL_PATH);

        $this->assertSame('/es/noticias/bienvenidos-a-nuestras-noticias', $esPath);
        $this->assertSame('/en/news/welcome-to-our-news', $enPath);
    }

    public function testCollectionDisplayTitleFallsBackToNameSlugThenKey(): void
    {
        $this->assertSame('Festivales de Títeres', collection_display_title([
            'listing_title' => '',
            'name' => 'Festivales de Títeres',
            'slug' => 'festivales-de-titeres',
            'collection_key' => 'festivales',
        ]));

        $this->assertSame('Festivales De Titeres', collection_display_title([
            'listing_title' => '',
            'name' => '',
            'slug' => 'festivales-de-titeres',
            'collection_key' => 'festivales',
        ]));

        $this->assertSame('Festivales', collection_display_title([
            'listing_title' => '',
            'name' => '',
            'slug' => '',
            'collection_key' => 'festivales',
        ]));
    }

    public function testCollectionDisplayIntroFallsBackToDescription(): void
    {
        $this->assertSame('Descripción principal', collection_display_intro([
            'listing_intro' => '',
            'description' => 'Descripción principal',
        ]));

        $this->assertSame('', collection_display_intro([
            'listing_intro' => '',
            'description' => '',
        ]));
    }
}

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
        $collection = [
            'slug' => '/news',
        ];

        $this->assertSame('/news', collection_url_path($collection));
    }

    public function testCanonicalPathMatchesAndReportsPrefix(): void
    {
        $collection = [
            'slug' => 'news',
        ];

        $info = collection_url_path_info($collection, 'news/welcome-to-our-news');

        $this->assertNotNull($info);
        $this->assertSame('/news', $info['prefix']);
        $this->assertSame('welcome-to-our-news', $info['remainder']);
    }

    public function testCanonicalPathReturnsNullWithoutSlug(): void
    {
        $collection = [
            'collection_key' => 'noticias',
        ];

        $this->assertSame('', collection_url_path($collection));
        $this->assertNull(collection_url_path_info($collection, 'noticias/bienvenidos-a-nuestras-noticias'));
    }

    public function testLocalizedCollectionUrlPathUsesTranslatedSlug(): void
    {
        $collection = [
            'slug' => 'news',
            'localized_slugs' => [
                'es' => 'noticias',
                'en' => 'news',
            ],
        ];

        $this->assertSame('/noticias', localized_collection_url_path($collection, 'es'));
        $this->assertSame('/news', localized_collection_url_path($collection, 'en'));
    }

    public function testLocalizedEntryUrlsUseTranslatedCollectionAndEntrySlugs(): void
    {
        $collection = [
            'slug' => 'news',
            'localized_slugs' => [
                'es' => 'noticias',
                'en' => 'news',
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
}

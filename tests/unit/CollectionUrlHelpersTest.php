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
}

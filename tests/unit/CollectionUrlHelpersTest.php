<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CollectionUrlHelpersTest extends CIUnitTestCase
{
    public function testCanonicalPrefixIsNormalized(): void
    {
        $collection = [
            'collection_key' => 'noticias',
            'url_prefix' => '/news',
        ];

        $this->assertSame('/news', collection_url_prefix($collection));
    }

    public function testCanonicalPathMatchesAndReportsPrefix(): void
    {
        $collection = [
            'collection_key' => 'noticias',
            'url_prefix' => '/news',
        ];

        $info = collection_url_path_info($collection, 'news/welcome-to-our-news');

        $this->assertNotNull($info);
        $this->assertSame('/news', $info['prefix']);
        $this->assertSame('welcome-to-our-news', $info['remainder']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\HtmlResponseCacheRegistry;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCache;

/** @internal */
final class HtmlResponseCacheRegistryTest extends CIUnitTestCase
{
    public function testInvalidatesOnlyMatchingScopeAndLocale(): void
    {
        $cache = new MockCache();
        $registry = new HtmlResponseCacheRegistry($cache);
        $targetKey = md5('GET:/es/noticias?page=1');
        $otherLocaleKey = md5('GET:/en/noticias?page=1');
        $otherScopeKey = md5('GET:/es/contacto');

        $registry->record('/es/noticias?page=1', 'es', ['entries'], $targetKey);
        $registry->record('/en/noticias?page=1', 'en', ['entries'], $otherLocaleKey);
        $registry->record('/es/contacto', 'es', ['forms'], $otherScopeKey);
        $cache->save($targetKey, 'target', 900);
        $cache->save($otherLocaleKey, 'other locale', 900);
        $cache->save($otherScopeKey, 'other scope', 900);

        $deleted = $registry->invalidate(['entries'], ['ES']);

        $this->assertSame(1, $deleted);
        $this->assertNull($cache->get($targetKey));
        $this->assertNotNull($cache->get($otherLocaleKey));
        $this->assertNotNull($cache->get($otherScopeKey));
    }

    public function testInvalidatesEveryQueryVariantRecordedForOneRoute(): void
    {
        $cache = new MockCache();
        $registry = new HtmlResponseCacheRegistry($cache);
        $firstKey = md5('GET:/es/noticias?page=1');
        $secondKey = md5('GET:/es/noticias?page=2');

        $registry->record('/es/noticias?page=1', 'es', ['entries'], $firstKey);
        $registry->record('/es/noticias?page=2', 'es', ['entries'], $secondKey);
        $cache->save($firstKey, 'page one', 900);
        $cache->save($secondKey, 'page two', 900);

        $deleted = $registry->invalidate(['entries'], [], ['/es/noticias']);

        $this->assertSame(2, $deleted);
        $this->assertNull($cache->get($firstKey));
        $this->assertNull($cache->get($secondKey));
    }
}

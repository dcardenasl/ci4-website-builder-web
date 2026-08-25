<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Cache;

/** @internal */
final class CacheTest extends CIUnitTestCase
{
    public function testPageCacheIncludesQueryStringInVariantIdentity(): void
    {
        $config = new Cache();

        self::assertTrue($config->cacheQueryString);
    }
}

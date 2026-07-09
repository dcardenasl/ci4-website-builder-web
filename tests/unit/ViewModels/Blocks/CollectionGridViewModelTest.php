<?php

declare(strict_types=1);

namespace Tests\Unit\ViewModels\Blocks;

use App\Services\SiteCollectionService;
use App\Services\SiteEntryService;
use App\ViewModels\Blocks\CollectionGridViewModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class CollectionGridViewModelTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset(true);

        parent::tearDown();
    }

    /**
     * @param list<array<string, mixed>>  $collections
     * @param array<string, mixed>        $entriesResult
     */
    private function mockServices(array $collections, array $entriesResult): void
    {
        $collectionService = $this->createMock(SiteCollectionService::class);
        $collectionService->method('getAll')->willReturn($collections);
        Services::injectMock('siteCollectionService', $collectionService);

        $entryService = $this->createMock(SiteEntryService::class);
        $entryService->method('list')->willReturn($entriesResult);
        Services::injectMock('siteEntryService', $entryService);
    }

    public function testResolvesCanonicalUrlAndEntries(): void
    {
        service('request')->setLocale('es');

        $this->mockServices(
            [[
                'collection_key' => 'news',
                'slug'           => 'noticias',
                'url_path'       => '/noticias',
                'index_page'     => [
                    'localized_slugs' => ['es' => 'noticias', 'en' => 'news'],
                ],
            ]],
            ['data' => [['title' => 'Post 1', 'slug' => 'post-1']], 'meta' => []]
        );

        $vm = new CollectionGridViewModel([
            'block_config' => ['collection_key' => 'news'],
            'block_data'   => ['section_title' => 'Noticias'],
        ], 'es');

        $vars = $vm->vars();

        $this->assertSame('news', $vars['collectionKey']);
        $this->assertCount(1, $vars['entries']);
        $this->assertNotSame('', $vars['canonicalViewAllUrl']);
    }

    public function testInvalidConfigFallsBackToSafeDefaults(): void
    {
        $this->mockServices([], ['data' => [], 'meta' => []]);

        $vm = new CollectionGridViewModel([
            'block_config' => [
                'collection_key'  => 'news',
                'order_by'        => 'DROP TABLE',
                'order_direction' => 'sideways',
                'layout_variant'  => 'bogus',
                'items_limit'     => 5000,
            ],
            'block_data' => ['view_all_url' => '/fallback'],
        ], 'es');

        $vars = $vm->vars();

        $this->assertSame('cards', $vars['layoutVariant']);
        $this->assertSame('/fallback', $vars['canonicalViewAllUrl'], 'Manual URL used when collection is unknown');
        $this->assertStringContainsString('md:grid-cols-3', $vars['gridClass']);
    }

    public function testEmptyCollectionKeySkipsServiceCalls(): void
    {
        // No mocks injected on purpose: with an empty key no service is touched.
        $vm = new CollectionGridViewModel([], 'es');

        $vars = $vm->vars();

        $this->assertSame('', $vars['collectionKey']);
        $this->assertSame([], $vars['entries']);
        $this->assertSame('', $vars['canonicalViewAllUrl']);
    }

    public function testPortfolioVariantChangesLayoutClasses(): void
    {
        $this->mockServices([], ['data' => [], 'meta' => []]);

        $vm = new CollectionGridViewModel([
            'block_config' => ['collection_key' => 'work', 'layout_variant' => 'portfolio'],
        ], 'es');

        $vars = $vm->vars();

        $this->assertStringContainsString('bg-slate-50/50', $vars['sectionClass']);
        $this->assertStringContainsString('lg:grid-cols-3', $vars['gridClass']);
    }
}

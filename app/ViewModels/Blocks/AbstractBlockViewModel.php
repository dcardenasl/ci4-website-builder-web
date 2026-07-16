<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

/**
 * Base class for block view models.
 *
 * A block view model receives the raw block payload from the Domain CMS and
 * prepares every derived value the template needs (parsing, validation,
 * defaults, URL building), so block views only print variables. Registered in
 * BlockRenderer::VIEW_MODELS; the returned vars() are merged into the view
 * data before rendering.
 *
 * `$context` also carries collaborators a specific view model needs (the
 * current request, a Site*Service) that BlockRenderer — the legitimate
 * composition boundary — resolves once per render pass. View models read
 * them via contextRequest()/contextService() instead of calling
 * `service()`/`Config\Services::x()` themselves, so they stay constructible
 * with plain arrays in tests (DEEP-WEB-02,
 * docs/plans/2026-07-10-plan-maestro-robustez-mantenibilidad.md).
 */
abstract class AbstractBlockViewModel
{
    /**
     * @param array<string, mixed> $block   Raw block payload (block_key, block_config, block_data, children)
     * @param array<string, mixed> $context Render-pass extras: formDefinition for form_embed,
     *                                      request/site*Service collaborators for blocks that need them
     */
    public function __construct(
        protected readonly array $block,
        protected readonly string $lang,
        protected readonly array $context = [],
    ) {
    }

    protected function contextRequest(): ?\CodeIgniter\HTTP\IncomingRequest
    {
        $value = $this->context['request'] ?? null;

        return $value instanceof \CodeIgniter\HTTP\IncomingRequest ? $value : null;
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T|null
     */
    protected function contextService(string $key, string $type): ?object
    {
        $value = $this->context[$key] ?? null;

        return $value instanceof $type ? $value : null;
    }

    /**
     * Variables to expose to the block template.
     *
     * @return array<string, mixed>
     */
    abstract public function vars(): array;

    /**
     * @return array<string, mixed>
     */
    protected function config(): array
    {
        return is_array($this->block['block_config'] ?? null) ? $this->block['block_config'] : [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function data(): array
    {
        return is_array($this->block['block_data'] ?? null) ? $this->block['block_data'] : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function children(): array
    {
        $children = $this->block['children'] ?? [];

        return is_array($children) ? array_values(array_filter($children, 'is_array')) : [];
    }

    protected function configString(string $key, string $default = ''): string
    {
        $value = $this->config()[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    protected function dataString(string $key, string $default = ''): string
    {
        $value = $this->data()[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @return array{source_kind: string, file_id: int|null, url: string}
     */
    protected function dataMediaReference(string $key): array
    {
        return $this->normalizeMediaReference($this->data()[$key] ?? []);
    }

    /**
     * @return array{source_kind: string, file_id: int|null, url: string}
     */
    protected function configMediaReference(string $key): array
    {
        return $this->normalizeMediaReference($this->config()[$key] ?? []);
    }

    /**
     * @param mixed $value
     * @return array{source_kind: string, file_id: int|null, url: string}
     */
    protected function normalizeMediaReference(mixed $value): array
    {
        if (! is_array($value)) {
            $value = [];
        }

        $sourceKind = strtolower(trim((string) ($value['source_kind'] ?? '')));
        $fileId = is_numeric($value['file_id'] ?? null) ? (int) $value['file_id'] : null;
        $url = is_scalar($value['url'] ?? null) ? trim((string) $value['url']) : '';

        return [
            'source_kind' => $sourceKind !== '' ? $sourceKind : ($fileId !== null ? 'hub_file' : 'external_url'),
            'file_id' => $fileId,
            'url' => $url,
        ];
    }

    protected function configBool(string $key, bool $default): bool
    {
        if (! array_key_exists($key, $this->config())) {
            return $default;
        }

        $parsed = filter_var($this->config()[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $parsed ?? $default;
    }

    protected function configInt(string $key, int $default): int
    {
        $value = $this->config()[$key] ?? null;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * True while rendering the isolated block-preview page (`/blocks/preview`),
     * which entry-driven blocks use to substitute mock data when there's
     * nothing real to show yet.
     */
    protected function isPreviewRequest(): bool
    {
        return str_contains($this->contextRequest()?->getUri()->getPath() ?? '', 'blocks/preview');
    }

    /**
     * Find the first collection matching a predicate in an already-fetched
     * collections list. Both collection_grid and collection_listing need to
     * look a collection up (by key or by id, respectively) before resolving
     * its canonical URL via the global `localized_collection_url_path()`
     * helper — sharing the lookup here keeps that a single source of truth
     * instead of two independently-maintained copies (see the 2026-07-15
     * dead-link fix for what letting those drift apart costs in practice).
     *
     * @param array<array<string, mixed>> $collections
     * @param callable(array<string, mixed>): bool $matcher
     * @return array<string, mixed>|null
     */
    protected function findCollection(array $collections, callable $matcher): ?array
    {
        foreach ($collections as $collection) {
            if (is_array($collection) && $matcher($collection)) {
                return $collection;
            }
        }

        return null;
    }
}

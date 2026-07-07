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
 */
abstract class AbstractBlockViewModel
{
    /**
     * @param array<string, mixed> $block   Raw block payload (block_key, block_config, block_data, children)
     * @param array<string, mixed> $context Render-pass extras (e.g. formDefinition for form_embed)
     */
    public function __construct(
        protected readonly array $block,
        protected readonly string $lang,
        protected readonly array $context = [],
    ) {
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
}

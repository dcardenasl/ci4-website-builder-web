<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Adds editor-only DOM metadata to rendered blocks.
 *
 * The annotator is opt-in: public rendering never installs one, so the
 * visitor-facing markup remains unchanged. Fragments are retained for the
 * later partial-preview transport and are sanitized before DOM replacement.
 */
final class EditorBlockAnnotator
{
    /** @var array<string, string> Wrapped HTML keyed by editor reference. */
    private array $fragments = [];

    /**
     * @param array<string, mixed> $block
     */
    public function wrap(array $block, string $html): string
    {
        $reference = $block['editor_ref'] ?? null;
        if (! is_string($reference) || $reference === '') {
            return $html;
        }

        $wrapped = '<div data-block-instance="' . esc($reference, 'attr') . '"'
            . ' data-block-fallback="' . (($block['is_fallback'] ?? false) ? '1' : '0') . '">'
            . $html . '</div>';

        $this->fragments[$reference] = $wrapped;

        return $wrapped;
    }

    public function fragment(string $reference): ?string
    {
        $fragment = $this->fragments[$reference] ?? null;
        if ($fragment === null) {
            return null;
        }

        // Partial outerHTML replacement does not carry a CSP nonce. The full
        // preview already loaded the block styles, so omit inline styles from
        // fragments instead of creating a CSP bypass.
        return preg_replace('#<style\b[^>]*>.*?</style\s*>#is', '', $fragment) ?? $fragment;
    }
}

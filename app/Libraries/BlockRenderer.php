<?php

declare(strict_types=1);

namespace App\Libraries;

class BlockRenderer
{
    /** @var array<string, array<string, mixed>|null> form definitions pre-loaded per render pass */
    private array $formDefinitions = [];

    /**
     * Render an array of blocks to HTML.
     *
     * @param array<array<string, mixed>> $blocks Array of block data from the API
     * @return string Rendered HTML
     */
    public function render(array $blocks, string $lang = 'es'): string
    {
        $this->preloadFormDefinitions($blocks, $lang);

        $html = '';
        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block, $lang);
        }

        return $html;
    }

    /**
     * Render a single block and its children recursively.
     *
     * @param array<string, mixed> $block
     */
    private function renderBlock(array $block, string $lang): string
    {
        $blockKey = $block['block_key'] ?? 'unknown';
        $config   = $block['block_config'] ?? [];
        $data     = $block['block_data'] ?? [];
        $children = $block['children'] ?? [];

        $renderedChildren = '';
        foreach ($children as $child) {
            $renderedChildren .= $this->renderBlock($child, $lang);
        }

        $formDefinition = null;
        if ($blockKey === 'form_embed') {
            $formKey = (string) ($config['form_key'] ?? 'contact');
            $formDefinition = $this->formDefinitions[$formKey] ?? null;
        }

        $blockViewName = "blocks/{$blockKey}";
        if (! view_exists($blockViewName)) {
            $blockViewName = 'blocks/unknown';
        }

        return view($blockViewName, [
            'block'            => $block,
            'config'           => $config,
            'data'             => $data,
            'renderedChildren' => $renderedChildren,
            'lang'             => $lang,
            'formDefinition'   => $formDefinition,
        ]);
    }

    /**
     * Pre-load form definitions for all form_embed blocks found in the block tree.
     *
     * @param array<array<string, mixed>> $blocks
     */
    private function preloadFormDefinitions(array $blocks, string $lang): void
    {
        foreach ($blocks as $block) {
            if (($block['block_key'] ?? '') === 'form_embed') {
                $formKey = (string) (($block['block_config'] ?? [])['form_key'] ?? 'contact');
                if (! array_key_exists($formKey, $this->formDefinitions)) {
                    try {
                        $this->formDefinitions[$formKey] = \Config\Services::siteFormService()
                            ->getDefinition($lang, $formKey);
                    } catch (\Throwable) {
                        $this->formDefinitions[$formKey] = null;
                    }
                }
            }
            $children = $block['children'] ?? [];
            if ($children !== []) {
                $this->preloadFormDefinitions($children, $lang);
            }
        }
    }
}

/**
 * Helper function to check if a view file exists.
 */
function view_exists(string $view): bool
{
    $file = APPPATH . 'Views/' . str_replace('.', '/', $view) . '.php';
    if (is_file($file)) {
        return true;
    }

    $locator = \Config\Services::locator();
    return $locator->locateFile($view, 'Views') !== false;
}

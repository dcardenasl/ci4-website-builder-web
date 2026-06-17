<?php

declare(strict_types=1);

namespace App\Libraries;

class BlockRenderer
{
    /**
     * Render an array of blocks to HTML.
     *
     * @param array<array<string, mixed>> $blocks Array of block data from the API
     * @return string Rendered HTML
     */
    public function render(array $blocks, string $lang = 'es'): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block, $lang);
        }

        return $html;
    }

    /**
     * Render a single block and its children recursively.
     */
    private function renderBlock(array $block, string $lang): string
    {
        $blockKey = $block['block_key'] ?? 'unknown';
        $config = $block['block_config'] ?? [];
        $data = $block['block_data'] ?? [];
        $children = $block['children'] ?? [];

        // Recursively render children
        $renderedChildren = '';
        foreach ($children as $child) {
            $renderedChildren .= $this->renderBlock($child, $lang);
        }

        // Determine view to use
        $blockViewName = "blocks/{$blockKey}";

        // Check if view exists; fall back to 'unknown'
        if (!view_exists($blockViewName)) {
            $blockViewName = 'blocks/unknown';
        }

        // Render view with block context
        return view($blockViewName, [
            'block'             => $block,
            'config'            => $config,
            'data'              => $data,
            'renderedChildren'  => $renderedChildren,
            'lang'              => $lang,
        ]);
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

<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

use App\DTO\ListingProjection;
use App\Libraries\HtmlSanitizer;

/**
 * Shared projection boundary for collection_listing and collection_grid.
 *
 * Both blocks consume the same public entry payload. Keeping normalization and
 * projection here prevents one block from accidentally exposing a field that
 * the other block does not understand.
 */
trait ListingProjectionSupport
{
    /**
     * @param mixed $entries
     * @return list<array<string, mixed>>
     */
    protected function prepareListingProjectionEntries(mixed $entries, ListingProjection $projection): array
    {
        if (! is_array($entries)) {
            return [];
        }

        $normalized = [];
        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $content = is_array($entry['listing_content'] ?? null) ? $entry['listing_content'] : [];
            $image = is_array($content['image'] ?? null) ? $content['image'] : null;
            $action = is_array($content['secondary_action'] ?? null) ? $content['secondary_action'] : null;
            $richText = is_string($content['rich_text'] ?? null) ? trim($content['rich_text']) : '';
            $featuredImage = $this->mediaReferenceFromPayload($entry, 'featured_image');

            $entry['listing_content'] = [
                'rich_text' => $richText !== '' ? HtmlSanitizer::clean($richText) : '',
                'image' => $this->normalizeListingProjectionImage($image),
                'secondary_action' => $this->normalizeListingProjectionAction($action),
            ];
            $entry['featured_image'] = $featuredImage['url'] !== '' ? $featuredImage : null;
            $entry['listing_projection'] = $this->projectListingEntry($entry, $projection);
            $normalized[] = $entry;
        }

        return $normalized;
    }

    /**
     * @param list<string> $allowedColumns
     * @return array{0: string, 1: string}
     */
    protected function resolveListingProjectionOrder(
        ListingProjection $projection,
        string $orderBy,
        string $orderDirection,
        array $allowedColumns,
    ): array {
        $field = (string) ($projection->order['field'] ?? '');
        $column = str_starts_with($field, 'entry.') ? substr($field, 6) : '';
        if ($column !== '' && in_array($column, $allowedColumns, true)) {
            $orderBy = $column;
            $orderDirection = (string) ($projection->order['direction'] ?? $orderDirection);
        }

        return [$orderBy, $orderDirection];
    }

    /**
     * @param array<string, mixed> $entry
     * @return array<string, mixed>
     */
    private function projectListingEntry(array $entry, ListingProjection $projection): array
    {
        $slots = [];
        $slotDisplay = [];
        foreach (['title', 'subtitle', 'summary', 'date', 'image'] as $slot) {
            $reference = $projection->slots[$slot] ?? '';
            if ($reference === '') {
                continue;
            }

            $value = $this->projectListingValue($entry, $reference);
            $slots[$slot] = $value;
            $display = $this->displayListingProjectionValue($value);
            if ($display !== '') {
                $slotDisplay[$slot] = $display;
            }
        }

        $extras = [];
        foreach ($projection->extras as $extra) {
            $extraValue = $this->projectListingValue($entry, $extra['source']);
            if ($extraValue === null || $extraValue === '') {
                continue;
            }

            $display = $this->displayListingProjectionValue($extraValue);
            if ($display === '') {
                continue;
            }

            $extras[] = ['label' => $extra['label'], 'value' => $display];
        }

        return ['slots' => $slots, 'slot_display' => $slotDisplay, 'extras' => $extras];
    }

    /** @param array<string, mixed> $entry */
    private function projectListingValue(array $entry, string $reference): mixed
    {
        if (str_starts_with($reference, 'entry.')) {
            return $entry[substr($reference, 6)] ?? null;
        }

        return match ($reference) {
            'taxonomy.categories' => $entry['categories'] ?? [],
            'taxonomy.tags' => $entry['tags'] ?? [],
            default => null,
        };
    }

    private function displayListingProjectionValue(mixed $value): string
    {
        if (is_scalar($value)) {
            return trim((string) $value);
        }

        if (! is_array($value)) {
            return '';
        }

        $labels = [];
        foreach ($value as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['name'] ?? $item['title'] ?? $item['label'] ?? $item['slug'] ?? ''));
            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return implode(', ', $labels);
    }

    /**
     * @param array<string, mixed>|null $image
     * @return array{url: string, alt: string}|null
     */
    private function normalizeListingProjectionImage(?array $image): ?array
    {
        $url = is_string($image['url'] ?? null) ? trim($image['url']) : '';
        if ($url === '') {
            return null;
        }

        return [
            'url' => $url,
            'alt' => is_string($image['alt'] ?? null) ? trim($image['alt']) : '',
        ];
    }

    /**
     * @param array<string, mixed>|null $action
     * @return array{label: string, url: string}|null
     */
    private function normalizeListingProjectionAction(?array $action): ?array
    {
        $label = is_string($action['label'] ?? null) ? trim($action['label']) : '';
        $url = is_string($action['url'] ?? null) ? trim($action['url']) : '';
        if ($label === '' || $url === '') {
            return null;
        }

        return [
            'label' => $label,
            'url' => str_starts_with($url, '/') ? lang_url($url, $this->lang) : $url,
        ];
    }
}

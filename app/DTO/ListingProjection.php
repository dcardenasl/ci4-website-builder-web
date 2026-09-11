<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Normalizes the versioned listing_projection block configuration.
 *
 * The public reader only accepts fields that already exist in its stable entry
 * payload. This keeps editor configuration forward-compatible without ever
 * turning a user-controlled reference into a database column or HTML key.
 */
final readonly class ListingProjection
{
    public const VERSION = 1;

    /** @var array<string, list<string>> */
    public const SLOT_TYPES = [
        'title' => ['text', 'string'],
        'subtitle' => ['text', 'string', 'taxonomy'],
        'summary' => ['text', 'string'],
        'date' => ['date', 'datetime', 'text', 'string'],
        'image' => ['media_reference'],
    ];

    public const DIRECTIONS = ['asc', 'desc'];
    public const OPERATORS = ['equals', 'not_equals', 'contains', 'before', 'after', 'in'];
    public const MAX_EXTRAS = 6;
    public const MAX_FILTERS = 6;

    /** @param array<string, string> $slots @param list<array{source: string, label: string, operator: string}> $extras @param array{field: string, direction: string, public: bool} $order @param list<array{source: string, label: string, operator: string}> $filters */
    private function __construct(
        public array $slots,
        public array $extras,
        public array $order,
        public array $filters,
    ) {
    }

    public static function empty(): self
    {
        return new self([], [], ['field' => '', 'direction' => 'desc', 'public' => false], []);
    }

    public function isEmpty(): bool
    {
        return $this->slots === []
            && $this->extras === []
            && $this->filters === []
            && $this->order['field'] === '';
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'version' => self::VERSION,
            'slots' => $this->slots,
            'extras' => $this->extras,
            'order' => $this->order,
            'filters' => $this->filters,
        ];
    }

    public function slot(string $name): string
    {
        return $this->slots[$name] ?? '';
    }

    /** @return array<string, string> */
    public static function allowedFields(): array
    {
        return [
            'entry.title' => 'text', 'entry.excerpt' => 'text', 'entry.slug' => 'string',
            'entry.featured_image' => 'media_reference', 'entry.published_at' => 'date',
            'entry.created_at' => 'date', 'entry.sort_order' => 'number',
            'taxonomy.categories' => 'taxonomy', 'taxonomy.tags' => 'taxonomy',
        ];
    }

    public static function fromArray(mixed $raw, array $allowedFields, array $legacyConfig = []): self
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        $raw = is_array($raw) ? $raw : [];
        $rawSlots = is_array($raw['slots'] ?? null) ? $raw['slots'] : [];
        $slots = [];
        foreach (array_keys(self::SLOT_TYPES) as $slot) {
            $value = trim((string) ($rawSlots[$slot] ?? ''));
            if ($value !== '' && isset($allowedFields[$value]) && self::slotAccepts($slot, $allowedFields[$value])) {
                $slots[$slot] = $value;
            }
        }
        if (($slots['date'] ?? '') === '') {
            $legacy = self::legacyReference($legacyConfig['date_field'] ?? '', $allowedFields);
            if ($legacy !== '' && self::slotAccepts('date', $allowedFields[$legacy])) {
                $slots['date'] = $legacy;
            }
        }
        $orderRaw = is_array($raw['order'] ?? null) ? $raw['order'] : [];
        $orderField = trim((string) ($orderRaw['field'] ?? ''));
        if ($orderField !== '' && !isset($allowedFields[$orderField])) {
            $orderField = '';
        }
        if ($orderField === '') {
            $orderField = self::legacyReference($legacyConfig['order_by'] ?? '', $allowedFields);
        }
        $direction = strtolower(trim((string) ($orderRaw['direction'] ?? 'desc')));
        $order = ['field' => $orderField, 'direction' => in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc', 'public' => filter_var($orderRaw['public'] ?? false, FILTER_VALIDATE_BOOL)];

        return new self($slots, self::items($raw['extras'] ?? [], $allowedFields, self::MAX_EXTRAS), $order, self::items($raw['filters'] ?? [], $allowedFields, self::MAX_FILTERS));
    }

    /** @return list<array{source: string, label: string, operator: string}> */
    private static function items(mixed $items, array $allowedFields, int $max): array
    {
        if (!is_array($items)) {
            return [];
        }
        $result = [];
        foreach ($items as $item) {
            if (count($result) >= $max) {
                break;
            }
            if (!is_array($item)) {
                continue;
            }
            $source = trim((string) ($item['source'] ?? ''));
            if ($source === '' || !isset($allowedFields[$source])) {
                continue;
            }
            $operator = strtolower(trim((string) ($item['operator'] ?? 'equals')));
            $result[] = [
                'source' => $source,
                'label' => trim((string) ($item['label'] ?? '')),
                'operator' => in_array($operator, self::OPERATORS, true) ? $operator : 'equals',
            ];
        }
        return $result;
    }

    private static function slotAccepts(string $slot, string $type): bool
    {
        return in_array($type, self::SLOT_TYPES[$slot] ?? [], true);
    }

    private static function legacyReference(mixed $value, array $allowedFields): string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === 'auto') {
            return '';
        }
        if (isset($allowedFields[$value])) {
            return $value;
        }
        foreach (['listing.', 'field:'] as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = substr($value, strlen($prefix));
            }
        }
        foreach (array_keys($allowedFields) as $reference) {
            if (str_ends_with($reference, '.' . $value)) {
                return $reference;
            }
        }
        return '';
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClientInterface;

/** Adapts the Web renderer to the Domain editor-projection contract. */
final class EditorPreviewService
{
    public function __construct(private readonly WebApiClientInterface $apiClient)
    {
    }

    /**
     * @param array<string, mixed> $draft
     * @return array{ok: bool, status: int, data: mixed, meta: array<string, mixed>, messages: list<string>}
     */
    public function project(string $ownerType, int $ownerId, array $draft): array
    {
        $path = match ($ownerType) {
            'page' => 'public/editor-projection/pages/' . $ownerId,
            'entry' => 'public/editor-projection/entries/' . $ownerId,
            default => null,
        };

        if ($path === null || $ownerId < 1) {
            return [
                'ok' => false,
                'status' => 400,
                'data' => null,
                'meta' => [],
                'messages' => ['Invalid preview owner.'],
            ];
        }

        return $this->apiClient->post($path, $draft);
    }
}

<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * WebApiClient — HTTP client for the public website.
 *
 * Key differences from the admin ApiClient:
 * - No JWT authentication (public site reads only)
 * - Sends X-App-Key header with every request
 * - GET responses are cached in CI4 cache (default 300 s)
 * - No file upload capability
 */
class WebApiClient
{
    // Bump when the public API payload shape or seeded content changes in a way
    // that should invalidate every cached web page response.
    private const CACHE_SCHEMA_VERSION = 3;

    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct(
        string $baseUrl = '',
        string $apiKey = '',
        int $timeout = 10
    ) {
        if (! $baseUrl) {
            $baseUrl = (string) env('WEB_API_BASE_URL', '');
        }
        if (trim($baseUrl) === '') {
            throw new \LogicException(
                'Missing WEB_API_BASE_URL. Pass it to constructor or set in .env. '
                . 'Example: WEB_API_BASE_URL=http://localhost:8190'
            );
        }

        if (! $apiKey) {
            $apiKey = (string) env('WEB_API_KEY', '');
        }
        if (trim($apiKey) === '') {
            throw new \LogicException(
                'Missing WEB_API_KEY. Pass it to constructor or set in .env. '
                . 'This should be a registered API key from your domain API.'
            );
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey  = $apiKey;
        $this->timeout = $timeout;
    }

    /**
     * GET request with server-side caching.
     *
     * @param array<string,mixed> $query
     * @return array{ok:bool,status:int,data:mixed,meta:array<string,mixed>,messages:list<string>}
     */
    public function get(string $path, array $query = [], int $cacheTtl = 300, string $scope = 'general'): array
    {
        $url      = $this->buildUrl($path, $query);
        $cacheKey = 'web_api_v' . self::CACHE_SCHEMA_VERSION . '_' . $scope . '_' . md5($url . '|' . $this->currentLocale());
        $cache    = \Config\Services::cache();

        $cached = $cache->get($cacheKey);
        if ($cached !== null && is_array($cached)) {
            return $cached;
        }

        $result = $this->request('GET', $path, $query);

        if ($result['ok'] && $cacheTtl > 0) {
            $cache->save($cacheKey, $result, $cacheTtl);
        }

        return $result;
    }

    /**
     * POST request — not cached (used for contact form).
     *
     * @param array<string,mixed> $data
     * @return array{ok:bool,status:int,data:mixed,meta:array<string,mixed>,messages:list<string>}
     */
    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, [], $data);
    }

    /**
     * Core request executor.
     *
     * @param array<string,mixed> $query
     * @param array<string,mixed> $body
     * @return array{ok:bool,status:int,data:mixed,meta:array<string,mixed>,messages:list<string>}
     */
    private function request(string $method, string $path, array $query = [], array $body = []): array
    {
        $url = $this->buildUrl($path, $query);

        $ch = curl_init($url);
        if ($ch === false) {
            return $this->errorResult(0, 'Could not initialize cURL');
        }

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Accept-Language: ' . $this->currentLocale(),
        ];

        if ($this->apiKey !== '') {
            $headers[] = 'X-App-Key: ' . $this->apiKey;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

        if ($method === 'POST' && $body !== []) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return $this->errorResult(0, 'cURL error: ' . $error);
        }

        $decoded  = json_decode((string) $raw, true);
        $data     = is_array($decoded) ? ($decoded['data'] ?? $decoded) : null;
        $meta     = is_array($decoded) ? (array) ($decoded['meta'] ?? []) : [];
        $messages = $this->extractMessages($decoded);

        return [
            'ok'       => $status >= 200 && $status < 300,
            'status'   => $status,
            'data'     => $data,
            'meta'     => $meta,
            'messages' => $messages,
        ];
    }

    /**
     * @param array<string,mixed> $query
     */
    private function buildUrl(string $path, array $query = []): string
    {
        $base = $this->baseUrl . '/api/v1/' . ltrim($path, '/');

        if ($query !== []) {
            $base .= '?' . http_build_query($query);
        }

        return $base;
    }

    /**
     * @return array{ok:bool,status:int,data:null,meta:array<string,mixed>,messages:list<string>}
     */
    private function errorResult(int $status, string $message): array
    {
        return [
            'ok'       => false,
            'status'   => $status,
            'data'     => null,
            'meta'     => [],
            'messages' => [$message],
        ];
    }

    /**
     * @param mixed $decoded
     * @return list<string>
     */
    private function extractMessages(mixed $decoded): array
    {
        if (! is_array($decoded)) {
            return [];
        }

        $messages = [];

        foreach (['message', 'error', 'errors'] as $key) {
            if (! isset($decoded[$key])) {
                continue;
            }

            $val = $decoded[$key];
            if (is_string($val)) {
                $messages[] = $val;
            } elseif (is_array($val)) {
                foreach ($val as $v) {
                    if (is_string($v)) {
                        $messages[] = $v;
                    }
                }
            }
        }

        return $messages;
    }

    private function currentLocale(): string
    {
        $locale = service('request')->getLocale();

        return $locale !== '' ? $locale : 'es';
    }
}

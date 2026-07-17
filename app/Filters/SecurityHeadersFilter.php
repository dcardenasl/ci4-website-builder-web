<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Security Headers Filter — web
 *
 * Adds defense-in-depth HTTP headers to every response. Pairs with CI4's
 * native CSP (`Config\ContentSecurityPolicy`) and CSRF stack; does not
 * replace them.
 *
 * Mirrors the same filter in ci4-website-builder-admin.
 */
class SecurityHeadersFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'DENY');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), magnetometer=(), gyroscope=()'
        );

        // Keep the starter flexible for seeded remote media while still
        // constraining the dangerous surfaces that do not need broad access.
        // The allowlist can be tightened later via .env without touching code.
        $csp = implode('; ', [
            'object-src ' . $this->cspSources('CSP_OBJECT_SRC', ['self', 'http:', 'https:']),
            "base-uri 'self'",
            "frame-ancestors 'none'",
            'img-src ' . $this->cspSources('CSP_IMAGE_SRC', ['self', 'http:', 'https:', 'data:']),
            'frame-src ' . $this->cspSources('CSP_FRAME_SRC', ['self', 'http:', 'https:']),
            'media-src ' . $this->cspSources('CSP_MEDIA_SRC', ['self', 'http:', 'https:']),
        ]);
        $response->setHeader('Content-Security-Policy', $csp);

        if (ENVIRONMENT === 'production') {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * @param list<string> $defaultSources
     */
    private function cspSources(string $envKey, array $defaultSources): string
    {
        $raw = env($envKey);
        $sources = [];

        if (is_string($raw) && trim($raw) !== '') {
            $sources = preg_split('/[\s,]+/', trim($raw)) ?: [];
        }

        if ($sources === []) {
            $sources = $defaultSources;
        }

        $sources = array_values(array_filter(array_map([$this, 'normalizeCspSourceToken'], $sources), static fn(string $value): bool => $value !== ''));

        return implode(' ', $sources);
    }

    private function normalizeCspSourceToken(string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            return '';
        }

        return match (strtolower($token)) {
            'self', 'none', 'unsafe-inline', 'unsafe-eval', 'strict-dynamic', 'report-sample' => "'{$token}'",
            default => $token,
        };
    }
}

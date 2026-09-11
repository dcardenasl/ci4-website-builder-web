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
        $isEditorPreview = str_ends_with($request->getUri()->getPath(), '/_editor/preview');
        $panelOrigin = (string) config('App')->editorPanelOrigin;
        if ($isEditorPreview) {
            $response->noCache();
            $response->setHeader('X-Robots-Tag', 'noindex, nofollow');
        }

        $response->setHeader('X-Content-Type-Options', 'nosniff');
        if ($isEditorPreview && $panelOrigin !== '') {
            // X-Frame-Options cannot express an allowlist. CSP below is the
            // authoritative, exact-origin policy for this one response.
            $response->removeHeader('X-Frame-Options');
        } else {
            $response->setHeader('X-Frame-Options', 'DENY');
        }
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), magnetometer=(), gyroscope=()'
        );

        // Keep the starter flexible for seeded remote media while still
        // constraining the dangerous surfaces that do not need broad access.
        // The allowlist can be tightened later via .env (Config\App::$csp*)
        // without touching code.
        $appConfig = config('App');
        $frameAncestors = $isEditorPreview && $panelOrigin !== ''
            ? $panelOrigin
            : "'none'";
        $csp = implode('; ', [
            'object-src ' . $this->cspSources($appConfig->cspObjectSrc),
            "base-uri 'self'",
            'frame-ancestors ' . $frameAncestors,
            'img-src ' . $this->cspSources($appConfig->cspImageSrc),
            'frame-src ' . $this->cspSources($appConfig->cspFrameSrc),
            'media-src ' . $this->cspSources($appConfig->cspMediaSrc),
        ]);
        $response->setHeader('Content-Security-Policy', $csp);

        if (ENVIRONMENT === 'production') {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * @param list<string> $sources
     */
    private function cspSources(array $sources): string
    {
        $sources = array_values(array_filter(array_map([$this, 'normalizeCspSourceToken'], $sources), static fn (string $value): bool => $value !== ''));

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

<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Refreshes the readable CSRF cookie after PageCache has served a response.
 *
 * Public HTML may be cached, but the token cookie must be issued per browser.
 * Forms render only the configured token name and hydrate the value from this
 * cookie in `site.js`, so a cached HTML snapshot never contains a shared token.
 */
final class CsrfCookieFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): RequestInterface
    {
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        $token = service('security')->getHash();
        if (! is_string($token) || $token === '') {
            return $response;
        }

        $securityConfig = config('Security');
        $cookieConfig = config('Cookie');
        $response->setCookie(
            $securityConfig->readableCookieName,
            $token,
            $securityConfig->expires,
            '',
            '/',
            '',
            $cookieConfig->secure,
            false,
            $cookieConfig->samesite,
        );

        return $response;
    }
}

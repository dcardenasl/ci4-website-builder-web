<?php

declare(strict_types=1);

namespace App\Shared\Security;

/** Signs and verifies the canonical HMAC used by editor preview links. */
final class PreviewLink
{
    /** @return array{expires: int, sig: string}|null */
    public static function sign(string $type, string $identifier, int $ttlSeconds = 3600): ?array
    {
        $secret = self::secret();
        if ($secret === '' || $identifier === '' || $ttlSeconds < 1) {
            return null;
        }

        $expires = time() + $ttlSeconds;

        return [
            'expires' => $expires,
            'sig' => hash_hmac('sha256', self::canonical($type, $identifier, $expires), $secret),
        ];
    }

    public static function verify(string $type, string $identifier, ?string $expiresRaw, ?string $signatureRaw): bool
    {
        $secret = self::secret();
        if ($secret === '' || $identifier === '' || $expiresRaw === null || $expiresRaw === ''
            || $signatureRaw === null || $signatureRaw === '' || ! ctype_digit($expiresRaw)) {
            return false;
        }

        $expires = (int) $expiresRaw;
        if ($expires < time()) {
            return false;
        }

        $expected = hash_hmac('sha256', self::canonical($type, $identifier, $expires), $secret);

        return hash_equals($expected, $signatureRaw);
    }

    private static function secret(): string
    {
        return (string) (new \Config\App())->cmsPreviewSecret;
    }

    private static function canonical(string $type, string $identifier, int $expires): string
    {
        return $type . ':' . $identifier . ':' . $expires;
    }
}

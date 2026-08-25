<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Collapses concurrent cache misses for one key into a single origin fetch.
 * This is intentionally host-local, matching the existing file-cache scope.
 */
final class SingleFlightLock
{
    public function __construct(
        private readonly string $directory,
        private readonly float $waitTimeoutSeconds = 3.0,
        private readonly int $pollIntervalMicroseconds = 20_000,
    ) {
    }

    /**
     * The first caller executes $onMiss. Waiters recheck the cache after the
     * lock is released, and only fetch independently if the wait timed out or
     * the first fetch did not produce a cacheable value.
     *
     * @template T
     * @param callable(): (T|null) $onCacheRecheck
     * @param callable(): T $onMiss
     * @return T
     */
    public function single(string $key, callable $onCacheRecheck, callable $onMiss): mixed
    {
        $handle = $this->openLockFile($key);
        if ($handle === null) {
            return $onMiss();
        }

        try {
            if (! $this->acquireExclusive($handle)) {
                return $onMiss();
            }

            return $onCacheRecheck() ?? $onMiss();
        } finally {
            @flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @return resource|null */
    private function openLockFile(string $key)
    {
        if ($this->directory === '') {
            return null;
        }

        if (! is_dir($this->directory) && ! @mkdir($this->directory, 0750, true) && ! is_dir($this->directory)) {
            return null;
        }

        $path = rtrim($this->directory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.lock';
        $handle = @fopen($path, 'c');

        return $handle === false ? null : $handle;
    }

    /** @param resource $handle */
    private function acquireExclusive($handle): bool
    {
        $deadline = microtime(true) + $this->waitTimeoutSeconds;

        do {
            if (flock($handle, LOCK_EX | LOCK_NB)) {
                return true;
            }

            usleep($this->pollIntervalMicroseconds);
        } while (microtime(true) < $deadline);

        return false;
    }
}

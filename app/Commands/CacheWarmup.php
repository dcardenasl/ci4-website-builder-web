<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/** Warm public page cache snapshots before switching traffic to snapshot mode. */
final class CacheWarmup extends BaseCommand
{
    protected $group = 'Web';
    protected $name = 'cache:warmup';
    protected $description = 'Warm public HTML page cache snapshots with a file lock';
    protected $usage = 'php spark cache:warmup [--strict] [--urls="/es,/en"]';
    protected $options = [
        '--strict' => 'Return a non-zero exit code when any configured URL fails.',
        '--urls'   => 'Comma-separated local paths to warm; defaults to CACHE_WARMUP_URLS.',
    ];

    public function run(array $params = []): int
    {
        $strict = CLI::getOption('strict') !== null
            || (ENVIRONMENT === 'production' && config('App')->pageDeliveryMode === 'snapshot');
        $rawUrls = CLI::getOption('urls');
        $rawUrls = is_string($rawUrls) && trim($rawUrls) !== ''
            ? $rawUrls
            : config('App')->cacheWarmupUrls;
        $paths = $this->paths($rawUrls);

        if ($paths === []) {
            CLI::error('No warmup URLs configured. Set CACHE_WARMUP_URLS or pass --urls.');

            return $strict ? EXIT_CONFIG : EXIT_SUCCESS;
        }

        $lockDirectory = WRITEPATH . 'cache';
        if (! is_dir($lockDirectory) && ! mkdir($lockDirectory, 0755, true) && ! is_dir($lockDirectory)) {
            CLI::error('Could not create the cache directory: ' . $lockDirectory);

            return EXIT_ERROR;
        }

        $lock = fopen($lockDirectory . '/warmup.lock', 'c');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            CLI::error('Another cache warmup is already running.');

            return EXIT_ERROR;
        }

        try {
            $client = Services::curlrequest([
                'baseURI'     => rtrim((string) config('App')->baseURL, '/'),
                'timeout'     => 30,
                'http_errors' => false,
                'verify'      => ENVIRONMENT === 'production',
            ]);
            $failed = 0;

            foreach ($paths as $path) {
                try {
                    $response = $client->get($path, [
                        'headers' => ['Accept' => 'text/html'],
                    ]);
                    $status = $response->getStatusCode();
                    if ($status < 200 || $status >= 300) {
                        $failed++;
                        CLI::write(sprintf('✗ %s (%d)', $path, $status), 'red');
                        continue;
                    }

                    CLI::write(sprintf('✓ %s (%d)', $path, $status), 'green');
                } catch (\Throwable $exception) {
                    $failed++;
                    CLI::write(sprintf('✗ %s (%s)', $path, $exception->getMessage()), 'red');
                }
            }

            CLI::write(sprintf('Warmup complete: %d URL(s), %d failure(s).', count($paths), $failed));

            return $strict && $failed > 0 ? EXIT_ERROR : EXIT_SUCCESS;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** @return list<string> */
    private function paths(string $rawUrls): array
    {
        $paths = [];
        foreach (explode(',', $rawUrls) as $rawPath) {
            $path = trim($rawPath);
            if ($path === '') {
                continue;
            }

            $path = '/' . ltrim((string) (parse_url($path, PHP_URL_PATH) ?: $path), '/');
            if (! in_array($path, $paths, true)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}

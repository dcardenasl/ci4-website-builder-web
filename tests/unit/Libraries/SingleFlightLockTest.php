<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\SingleFlightLock;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class SingleFlightLockTest extends CIUnitTestCase
{
    private string $lockDir;

    protected function setUp(): void
    {
        parent::setUp();
        $baseDir = defined('WRITEPATH') ? WRITEPATH : './writable/';
        $this->lockDir = rtrim($baseDir, '/') . '/cache/test_locks_' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->lockDir)) {
            $files = glob($this->lockDir . '/*');
            if (is_array($files)) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
            @rmdir($this->lockDir);
        }

        parent::tearDown();
    }

    public function testSingleFlightExecutesMissWhenCacheIsEmpty(): void
    {
        $lock = new SingleFlightLock($this->lockDir);
        $missCalled = 0;

        $result = $lock->single(
            'test-key',
            static fn (): ?string => null,
            function () use (&$missCalled): string {
                $missCalled++;

                return 'fresh-data';
            },
        );

        $this->assertSame('fresh-data', $result);
        $this->assertSame(1, $missCalled);
    }

    public function testSingleFlightReturnsRecheckedCacheValue(): void
    {
        $lock = new SingleFlightLock($this->lockDir);
        $missCalled = 0;

        $result = $lock->single(
            'test-key',
            static fn (): string => 'cached-data',
            function () use (&$missCalled): string {
                $missCalled++;

                return 'fresh-data';
            },
        );

        $this->assertSame('cached-data', $result);
        $this->assertSame(0, $missCalled);
    }

    public function testSingleFlightFallsBackAfterBoundedWait(): void
    {
        $lock = new SingleFlightLock($this->lockDir, 0.02, 1_000);
        $key = 'held-key';
        mkdir($this->lockDir, 0750, true);
        $handle = fopen($this->lockDir . '/' . hash('sha256', $key) . '.lock', 'c');
        $this->assertNotFalse($handle);
        $this->assertTrue(flock($handle, LOCK_EX));

        $missCalled = 0;
        $result = $lock->single(
            $key,
            static fn (): string => 'not-rechecked',
            function () use (&$missCalled): string {
                $missCalled++;

                return 'fallback-data';
            },
        );

        $this->assertSame('fallback-data', $result);
        $this->assertSame(1, $missCalled);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Analytics\AnalyticsTransportInterface;
use App\Analytics\AnalyticsTransportResult;
use App\Libraries\AnalyticsQueue;
use PHPUnit\Framework\TestCase;

/** @internal */
final class AnalyticsQueueTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/ci4-analytics-' . bin2hex(random_bytes(8));
        mkdir($this->directory, 0750, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->directory);
        parent::tearDown();
    }

    public function testEnqueueWritesLocallyAndFlushSendsThenDeletesEvent(): void
    {
        $transport = new FakeAnalyticsTransport();
        $queue = new AnalyticsQueue($this->directory, 5, $transport);

        $this->assertTrue($queue->enqueue(['url' => '/es', 'session_id' => 'visitor-1']));
        $this->assertCount(1, glob($this->directory . '/pending/*.json') ?: []);

        $report = $queue->flush(10);

        $this->assertFalse($report->locked);
        $this->assertSame(1, $report->processed);
        $this->assertSame(1, $report->sent);
        $this->assertSame(0, $report->remaining);
        $this->assertCount(0, glob($this->directory . '/pending/*.json') ?: []);
        $this->assertSame([['url' => '/es', 'session_id' => 'visitor-1']], $transport->payloads);
    }

    public function testRetryableFailureIsQuarantinedAfterMaximumAttempts(): void
    {
        $queue = new AnalyticsQueue(
            $this->directory,
            1,
            new FakeAnalyticsTransport([AnalyticsTransportResult::retryable()]),
        );

        $this->assertTrue($queue->enqueue(['url' => '/es']));
        $report = $queue->flush(10);

        $this->assertSame(1, $report->failed);
        $this->assertSame(0, $report->remaining);
        $this->assertCount(1, glob($this->directory . '/failed/*.json') ?: []);
    }

    public function testSecondFlushCannotRunWhileAnotherProcessOwnsTheLock(): void
    {
        mkdir($this->directory . '/pending', 0750, true);
        $lock = fopen($this->directory . '/flush.lock', 'c');
        $this->assertIsResource($lock);
        $this->assertTrue(flock($lock, LOCK_EX | LOCK_NB));

        try {
            $report = (new AnalyticsQueue(
                $this->directory,
                5,
                new FakeAnalyticsTransport(),
            ))->flush(10);

            $this->assertTrue($report->locked);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $directory . '/' . $entry;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($directory);
    }
}

final class FakeAnalyticsTransport implements AnalyticsTransportInterface
{
    /** @var list<AnalyticsTransportResult> */
    private array $responses;

    /** @var list<array<string, mixed>> */
    public array $payloads = [];

    /** @param list<AnalyticsTransportResult> $responses */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    /** @param array<string, mixed> $payload */
    public function send(array $payload): AnalyticsTransportResult
    {
        $this->payloads[] = $payload;

        return array_shift($this->responses) ?? AnalyticsTransportResult::accepted(204);
    }
}

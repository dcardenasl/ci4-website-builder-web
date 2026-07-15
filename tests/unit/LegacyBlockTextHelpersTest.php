<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LegacyBlockTextHelpersTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        legacy_block_text_key_usage_count('reset');
    }

    public function testCounterStartsAtZero(): void
    {
        $this->assertSame(0, legacy_block_text_key_usage_count('read'));
    }

    public function testCanonicalContentKeyDoesNotIncrementCounter(): void
    {
        block_text_content(['content' => '<p>Hello</p>']);

        $this->assertSame(0, legacy_block_text_key_usage_count('read'));
    }

    public function testLegacyBodyKeyIncrementsCounter(): void
    {
        block_text_content(['body' => '<p>Hello</p>']);

        $this->assertSame(1, legacy_block_text_key_usage_count('read'));
    }

    public function testLegacyHtmlKeyIncrementsCounter(): void
    {
        block_text_content(['html' => '<p>Hello</p>']);

        $this->assertSame(1, legacy_block_text_key_usage_count('read'));
    }

    public function testCounterAccumulatesAcrossCalls(): void
    {
        block_text_content(['body' => '<p>One</p>']);
        block_text_content(['html' => '<p>Two</p>']);
        block_text_content(['content' => '<p>Three</p>']);

        $this->assertSame(2, legacy_block_text_key_usage_count('read'));
    }

    public function testResetClearsCounter(): void
    {
        block_text_content(['body' => '<p>Hello</p>']);
        $this->assertSame(1, legacy_block_text_key_usage_count('read'));

        legacy_block_text_key_usage_count('reset');

        $this->assertSame(0, legacy_block_text_key_usage_count('read'));
    }
}

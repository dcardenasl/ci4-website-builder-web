<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Reports how many times `block_text_content()` (app/Common.php) has served
 * a legacy `body`/`html` field instead of the canonical `content` field,
 * since the counter was last reset. Part of DEBT-002 (see root TASKS.md):
 * once this stays at 0 across a representative production window, the
 * legacy fallback (here and in domain's BlockTextPayload::normalize()) can
 * be deleted.
 */
class ReportLegacyBlockTextUsage extends BaseCommand
{
    protected $group       = 'Web';
    protected $name        = 'legacy:block-text-report';
    protected $description = 'Report (or reset) the legacy content/body/html fallback usage counter (DEBT-002).';
    protected $usage       = 'php spark legacy:block-text-report [--reset]';
    protected $options     = [
        '--reset' => 'Reset the counter to 0 after reporting it.',
    ];

    public function run(array $params): void
    {
        $count = legacy_block_text_key_usage_count('read');
        CLI::write("Legacy body/html field usage since last reset: {$count}", $count > 0 ? 'yellow' : 'green');

        if (array_key_exists('reset', $params) || in_array('--reset', $params, true)) {
            legacy_block_text_key_usage_count('reset');
            CLI::write('Counter reset to 0.', 'cyan');
        }
    }
}

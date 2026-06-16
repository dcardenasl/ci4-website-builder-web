<?php if (ENVIRONMENT !== 'production'): ?>
    <div class="bg-yellow-100 border-2 border-yellow-400 p-4 my-4 rounded">
        <p class="text-yellow-800 font-semibold">
            Block type not found: <code><?= esc($block['block_key'] ?? 'unknown') ?></code>
        </p>
        <p class="text-yellow-700 text-sm mt-2">
            Create a view at: <code>app/Views/blocks/<?= esc($block['block_key'] ?? 'unknown') ?>.php</code>
        </p>
    </div>
<?php else: ?>
    <!-- Block: <?= esc($block['block_key'] ?? 'unknown') ?> -->
<?php endif; ?>

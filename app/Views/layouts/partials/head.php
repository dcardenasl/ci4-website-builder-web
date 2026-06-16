<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= esc($pageTitle ?? $settings['site_title'] ?? 'Website') ?></title>

<?php if (!empty($metaDescription)): ?>
    <meta name="description" content="<?= esc($metaDescription) ?>">
<?php elseif (!empty($settings['site_description'])): ?>
    <meta name="description" content="<?= esc($settings['site_description']) ?>">
<?php endif; ?>

<?php if (!empty($metaRobots)): ?>
    <meta name="robots" content="<?= esc($metaRobots) ?>">
<?php else: ?>
    <meta name="robots" content="index, follow">
<?php endif; ?>

<?php if (!empty($canonicalUrl)): ?>
    <link rel="canonical" href="<?= esc($canonicalUrl) ?>">
<?php endif; ?>

<?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?= esc($ogImage) ?>">
<?php endif; ?>

<meta property="og:title" content="<?= esc($pageTitle ?? $settings['site_title'] ?? 'Website') ?>">
<?php if (!empty($metaDescription)): ?>
    <meta property="og:description" content="<?= esc($metaDescription) ?>">
<?php endif; ?>

<?php if (!empty($schemaData)): ?>
    <script type="application/ld+json">
        <?= json_encode($schemaData) ?>
    </script>
<?php endif; ?>

<link rel="stylesheet" href="<?= base_url('assets/css/compiled.css') ?>">

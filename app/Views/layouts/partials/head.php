<?php
$siteConfig = config('App');
$supportedLocales = $siteConfig->supportedLocales ?? [];
$defaultLocale = $siteConfig->defaultLocale ?? ($supportedLocales[0] ?? service('request')->getLocale());
$resolvedTitle = $pageTitle ?? $settings['site_title'] ?? 'Website';
$resolvedDescription = $metaDescription ?? $settings['site_description'] ?? trim($resolvedTitle);

if ($resolvedDescription === '') {
    $resolvedDescription = $resolvedTitle;
}

$resolvedSchemaData = $schemaData;
if (! is_array($resolvedSchemaData) || $resolvedSchemaData === []) {
    $resolvedSchemaData = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $resolvedTitle,
        'url' => $canonicalUrl ?? site_url(service('request')->getPath()),
        'description' => $resolvedDescription,
    ];
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= esc($resolvedTitle) ?></title>
<meta name="description" content="<?= esc($resolvedDescription) ?>">

<meta name="robots" content="<?= esc($metaRobots ?? 'index, follow') ?>">

<?php if (! empty($canonicalUrl)): ?>
    <link rel="canonical" href="<?= esc($canonicalUrl) ?>">
<?php endif; ?>

<?php foreach ($supportedLocales as $locale): ?>
    <link rel="alternate" hreflang="<?= esc($locale) ?>" href="<?= esc(current_lang_url($locale)) ?>">
<?php endforeach; ?>
<?php if (! empty($defaultLocale)): ?>
    <link rel="alternate" hreflang="x-default" href="<?= esc(current_lang_url($defaultLocale)) ?>">
<?php endif; ?>

<?php if (! empty($ogImage)): ?>
    <meta property="og:image" content="<?= esc($ogImage) ?>">
<?php endif; ?>

<meta property="og:title" content="<?= esc($resolvedTitle) ?>">
<meta property="og:description" content="<?= esc($resolvedDescription) ?>">
<meta property="og:type" content="website">

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?= esc($resolvedTitle) ?>">
<meta name="twitter:description" content="<?= esc($resolvedDescription) ?>">
<?php if (! empty($ogImage)): ?>
    <meta name="twitter:image" content="<?= esc($ogImage) ?>">
<?php endif; ?>

<?php if (! empty($resolvedSchemaData)): ?>
    <script type="application/ld+json">
        <?= json_encode($resolvedSchemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    </script>
<?php endif; ?>

<?php
$compiledCssPath = FCPATH . 'assets/css/compiled.css';
$compiledCssVersion = is_file($compiledCssPath) ? (string) filemtime($compiledCssPath) : (string) time();
?>

<link rel="stylesheet" href="<?= base_url('assets/css/compiled.css?v=' . $compiledCssVersion) ?>">

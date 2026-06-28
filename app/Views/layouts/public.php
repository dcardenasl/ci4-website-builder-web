<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <?= view('layouts/partials/head', $data ?? []) ?>
</head>
<body>
    <?= view('layouts/partials/header', ['menu' => $mainMenu ?? [], 'settings' => $settings ?? []]) ?>

    <main>
        <?= view($view, $data ?? []) ?>
    </main>

    <?= view('layouts/partials/footer', ['menu' => $footerMenu ?? [], 'settings' => $settings ?? []]) ?>
    <?= view('layouts/partials/flash_messages') ?>
</body>
</html>

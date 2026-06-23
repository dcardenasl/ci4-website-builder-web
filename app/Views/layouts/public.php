<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <?= view('layouts/partials/head', $data ?? []) ?>
</head>
<body>
    <?= view('layouts/partials/header', ['menu' => $mainMenu ?? []]) ?>

    <main>
        <?= view($view, $data ?? []) ?>
    </main>

    <?= view('layouts/partials/footer', ['menu' => $footerMenu ?? [], 'settings' => $settings ?? []]) ?>
    <?= view('layouts/partials/flash_messages') ?>
    <?php
    $recaptchaSiteKey = (string) env('RECAPTCHA_SITE_KEY', '');
    if ($recaptchaSiteKey !== ''):
    ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchaSiteKey) ?>"></script>
    <script>
    (function () {
        var form = document.getElementById('contact-form');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('[type=submit]');
            if (btn) btn.disabled = true;
            grecaptcha.ready(function () {
                grecaptcha.execute('<?= esc($recaptchaSiteKey) ?>', { action: 'contact' }).then(function (token) {
                    var field = document.getElementById('g_recaptcha_response');
                    if (field) field.value = token;
                    form.submit();
                });
            });
        });
    })();
    </script>
    <?php endif; ?>
</body>
</html>

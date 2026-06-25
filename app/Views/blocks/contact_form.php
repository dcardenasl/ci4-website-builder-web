<?php
/**
 * contact_form block — dynamic form renderer driven by Domain CMS form definition.
 *
 * Variables injected by BlockRenderer:
 * @var array<string, mixed>      $config         Block config (form_key, css_class, show_info_boxes, ...)
 * @var array<string, mixed>      $data           Block data (heading, description, info_email_label, ...)
 * @var array<string, mixed>|null $formDefinition Form definition from Domain API (null = unavailable)
 * @var string                    $lang           Current locale code
 */

$formKey      = (string) ($config['form_key'] ?? 'contact');
$cssClass     = (string) ($config['css_class'] ?? '');
$showInfoBoxes = ! isset($config['show_info_boxes']) || ($config['show_info_boxes'] !== false && $config['show_info_boxes'] !== 'false');

$heading          = (string) ($data['heading'] ?? '');
$description      = (string) ($data['description'] ?? '');
$infoEmailLabel   = (string) ($data['info_email_label'] ?? '');
$infoEmailDesc    = (string) ($data['info_email_desc'] ?? '');
$infoPhoneLabel   = (string) ($data['info_phone_label'] ?? '');
$infoPhoneDesc    = (string) ($data['info_phone_desc'] ?? '');

// Flash data keyed by form_key so multiple forms on the same page don't collide
$sent    = session()->getFlashdata("form_sent_{$formKey}");
$errors  = (array) (session()->getFlashdata("form_errors_{$formKey}") ?? []);

// Resolve form definition: injected by BlockRenderer, or lazy fallback
if (! isset($formDefinition)) {
    $formDefinition = null;
}
if ($formDefinition === null) {
    try {
        $formDefinition = \Config\Services::siteFormService()->getDefinition($lang ?? 'es', $formKey);
    } catch (\Throwable) {
        $formDefinition = null;
    }
}

$fields      = (array) ($formDefinition['fields'] ?? []);
$submitLabel = (string) ($formDefinition['submit_label'] ?? 'Enviar');
$successMsg  = (string) ($formDefinition['success_message'] ?? '¡Mensaje enviado! Nos pondremos en contacto pronto.');
$hasCaptcha  = ! empty($formDefinition['has_captcha']);
$recaptchaSiteKey = (string) \Config\Services::siteSettingsService()
    ->get('recaptcha_site_key', env('RECAPTCHA_SITE_KEY', ''));

$inputClass = 'form-input rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-none';
?>

<section class="py-12 sm:py-14 <?= esc($cssClass) ?>">
    <div class="container-base">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-start">

            <?php /* ── Left column: heading + info boxes ───────────────────── */ ?>
            <div class="space-y-5">
                <?php if ($heading !== ''): ?>
                    <h2 class="section-title text-3xl sm:text-4xl"><?= esc($heading) ?></h2>
                <?php endif; ?>
                <?php if ($description !== ''): ?>
                    <p class="section-copy max-w-xl text-base"><?= esc($description) ?></p>
                <?php endif; ?>

                <?php if ($sent): ?>
                    <div class="surface-card-compact border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <?= esc($successMsg) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors['_form'])): ?>
                    <div class="surface-card-compact border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <?= esc($errors['_form']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($showInfoBoxes && ($infoEmailLabel !== '' || $infoPhoneLabel !== '')): ?>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <?php if ($infoEmailLabel !== ''): ?>
                            <div class="surface-card-compact px-4 py-4">
                                <p class="section-title text-sm"><?= esc($infoEmailLabel) ?></p>
                                <?php if ($infoEmailDesc !== ''): ?>
                                    <p class="section-copy mt-1 text-sm"><?= esc($infoEmailDesc) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($infoPhoneLabel !== ''): ?>
                            <div class="surface-card-compact px-4 py-4">
                                <p class="section-title text-sm"><?= esc($infoPhoneLabel) ?></p>
                                <?php if ($infoPhoneDesc !== ''): ?>
                                    <p class="section-copy mt-1 text-sm"><?= esc($infoPhoneDesc) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php /* ── Right column: form ──────────────────────────────────── */ ?>
            <div class="pt-6 lg:pl-10 lg:pt-0">
                <?php if ($formDefinition === null): ?>
                    <?php /* Form unavailable — degrade gracefully, no error exposed to public */ ?>
                <?php elseif ($sent): ?>
                    <?php /* Form already submitted — hide the form, success message shown above */ ?>
                <?php else: ?>
                    <div class="surface-card p-6 sm:p-8 shadow-sm">
                        <form method="post"
                              action="<?= site_url("forms/{$formKey}/submit") ?>"
                              class="space-y-5"
                              id="form-<?= esc($formKey) ?>">
                            <?= csrf_field() ?>

                            <?php if ($hasCaptcha && $recaptchaSiteKey !== ''): ?>
                                <input type="hidden" name="g_recaptcha_response" id="g_recaptcha_response_<?= esc($formKey) ?>" />
                            <?php endif; ?>

                            <?php foreach ($fields as $field): ?>
                                <?php
                                $fKey         = (string) ($field['field_key'] ?? '');
                                $fType        = (string) ($field['field_type'] ?? 'text');
                                $label        = (string) ($field['label'] ?? $fKey);
                                $placeholder  = (string) ($field['placeholder'] ?? '');
                                $helpText     = (string) ($field['help_text'] ?? '');
                                $isRequired   = ! empty($field['is_required']);
                                $fieldError   = $errors[$fKey] ?? '';
                                $inputId      = 'cf_' . esc($formKey) . '_' . esc($fKey);
                                $oldValue     = esc((string) old($fKey, ''));
                                $errorClass   = $fieldError !== '' ? ' !border-rose-400 !ring-1 !ring-rose-400' : '';
                                ?>
                                <div>
                                    <label for="<?= $inputId ?>" class="mb-2 block text-sm font-medium text-slate-700">
                                        <?= esc($label) ?>
                                        <?php if ($isRequired): ?><span class="text-rose-500"> *</span><?php endif; ?>
                                    </label>

                                    <?php if ($fType === 'textarea'): ?>
                                        <textarea id="<?= $inputId ?>"
                                                  name="<?= esc($fKey) ?>"
                                                  <?= $isRequired ? 'required' : '' ?>
                                                  rows="6"
                                                  placeholder="<?= esc($placeholder) ?>"
                                                  class="<?= $inputClass . $errorClass ?> block min-h-[9rem] w-full resize-none"><?= $oldValue ?></textarea>
                                    <?php else: ?>
                                        <input type="<?= esc($fType) ?>"
                                               id="<?= $inputId ?>"
                                               name="<?= esc($fKey) ?>"
                                               <?= $isRequired ? 'required' : '' ?>
                                               placeholder="<?= esc($placeholder) ?>"
                                               value="<?= $oldValue ?>"
                                               class="<?= $inputClass . $errorClass ?> w-full" />
                                    <?php endif; ?>

                                    <?php if ($fieldError !== ''): ?>
                                        <p class="mt-1 text-xs text-rose-600"><?= esc($fieldError) ?></p>
                                    <?php elseif ($helpText !== ''): ?>
                                        <span class="mt-1 block text-xs text-slate-400 font-normal"><?= esc($helpText) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <button type="submit"
                                    class="btn btn-primary w-full rounded-xl px-6 py-3.5 text-sm font-semibold">
                                <?= esc($submitLabel) ?>
                            </button>
                        </form>
                    </div>

                    <?php if ($hasCaptcha && $recaptchaSiteKey !== ''): ?>
                        <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var form = document.getElementById('form-<?= esc($formKey) ?>');
                            if (!form) return;
                            form.addEventListener('submit', function (e) {
                                e.preventDefault();
                                var btn = form.querySelector('button[type=submit]');
                                if (btn) btn.disabled = true;
                                grecaptcha.ready(function () {
                                    grecaptcha.execute('<?= esc($recaptchaSiteKey) ?>', { action: 'submit' })
                                        .then(function (token) {
                                            var input = document.getElementById('g_recaptcha_response_<?= esc($formKey) ?>');
                                            if (input) input.value = token;
                                            form.submit();
                                        })
                                        .catch(function () {
                                            if (btn) btn.disabled = false;
                                        });
                                });
                            });
                        });
                        </script>
                        <?php if (! defined('RECAPTCHA_SCRIPT_LOADED')): ?>
                            <?php define('RECAPTCHA_SCRIPT_LOADED', true); ?>
                            <script src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchaSiteKey) ?>" async defer></script>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * contact_form block — HTML form for /contacto/enviar.
 *
 * @var array<string, mixed> $config
 * @var array<string, mixed> $data
 */
$heading        = $data['heading'] ?? '';
$description    = $data['description'] ?? '';
$labelCompany   = $data['label_company'] ?? '';
$labelName      = $data['label_name'] ?? '';
$labelEmail     = $data['label_email'] ?? '';
$labelPhone     = $data['label_phone'] ?? '';
$phonePrefix    = $data['phone_prefix'] ?? '';
$labelMessage   = $data['label_message'] ?? '';
$infoEmailLabel = $data['info_email_label'] ?? '';
$infoEmailDesc  = $data['info_email_desc'] ?? '';
$infoPhoneLabel = $data['info_phone_label'] ?? '';
$infoPhoneDesc  = $data['info_phone_desc'] ?? '';
$submitLabel    = $data['submit_label'] ?? '';
$successMessage = $data['success_message'] ?? '';
$showCompany    = ! empty($config['show_company']) && $config['show_company'] !== false && $config['show_company'] !== 'false';
$showInfoBoxes  = ! isset($config['show_info_boxes']) || ($config['show_info_boxes'] !== false && $config['show_info_boxes'] !== 'false');
$cssClass       = $config['css_class'] ?? '';

$sent  = session()->getFlashdata('contact_sent');
$error = session()->getFlashdata('contact_error');
?>
<section class="py-12 sm:py-14 <?= esc($cssClass) ?>">
    <div class="container-base">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-start">
            <div class="space-y-5">
                <?php if ($heading): ?>
                    <h2 class="section-title text-3xl sm:text-4xl">
                        <?= esc($heading) ?>
                    </h2>
                <?php endif; ?>

                <?php if ($description): ?>
                    <p class="section-copy max-w-xl text-base">
                        <?= esc($description) ?>
                    </p>
                <?php endif; ?>

                <?php if ($sent): ?>
                    <div class="surface-card-compact border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <?= esc($successMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="surface-card-compact border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <?= esc($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($showInfoBoxes && ($infoEmailLabel || $infoPhoneLabel)): ?>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <?php if ($infoEmailLabel): ?>
                            <div class="surface-card-compact px-4 py-4">
                                <p class="section-title text-sm"><?= esc($infoEmailLabel) ?></p>
                                <?php if ($infoEmailDesc): ?>
                                    <p class="section-copy mt-1 text-sm"><?= esc($infoEmailDesc) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($infoPhoneLabel): ?>
                            <div class="surface-card-compact px-4 py-4">
                                <p class="section-title text-sm"><?= esc($infoPhoneLabel) ?></p>
                                <?php if ($infoPhoneDesc): ?>
                                    <p class="section-copy mt-1 text-sm"><?= esc($infoPhoneDesc) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="surface-default pt-6 lg:pl-10 lg:pt-0">
                <form method="post" action="<?= lang_url('/contacto/enviar') ?>" class="space-y-5" id="contact-form">
                    <?= csrf_field() ?>
                    <?php if ((string) env('RECAPTCHA_SITE_KEY', '') !== ''): ?>
                        <input type="hidden" name="g_recaptcha_response" id="g_recaptcha_response" />
                    <?php endif; ?>

                    <?php if ($showCompany): ?>
                        <div>
                            <label for="cf_company" class="mb-2 block text-sm font-medium text-slate-700">
                                <?= esc($labelCompany ?: 'Empresa') ?>
                            </label>
                            <input type="text"
                                   id="cf_company"
                                   name="company"
                                   placeholder="Nombre de su empresa"
                                   class="form-input rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-none" />
                        </div>
                    <?php endif; ?>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="cf_name" class="mb-2 block text-sm font-medium text-slate-700">
                                <?= esc($labelName ?: 'Nombre') ?> <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="cf_name"
                                   name="name"
                                   required
                                   placeholder="Su nombre completo"
                                   class="form-input rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-none" />
                        </div>

                        <div>
                            <label for="cf_email" class="mb-2 block text-sm font-medium text-slate-700">
                                <?= esc($labelEmail ?: 'Email') ?> <span class="text-rose-500">*</span>
                            </label>
                            <input type="email"
                                   id="cf_email"
                                   name="email"
                                   required
                                   placeholder="correo@ejemplo.com"
                                   class="form-input rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-none" />
                        </div>
                    </div>

                    <div>
                        <label for="cf_phone" class="mb-2 block text-sm font-medium text-slate-700"><?= esc($labelPhone ?: 'Teléfono') ?></label>
                        <div class="flex gap-3">
                            <span class="inline-flex items-center rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-500 select-none">
                                <?= esc($phonePrefix) ?>
                            </span>
                            <input type="tel"
                                   id="cf_phone"
                                   name="phone"
                                   placeholder="9 1234 5678"
                                   class="form-input min-w-0 flex-1 rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-none" />
                        </div>
                    </div>

                    <div>
                        <label for="cf_message" class="mb-2 block text-sm font-medium text-slate-700">
                            <?= esc($labelMessage ?: 'Mensaje') ?> <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="cf_message"
                                  name="message"
                                  required
                                  rows="7"
                                  placeholder="Escriba su mensaje aquí..."
                                  class="form-input block min-h-[11rem] w-full resize-none rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-none"></textarea>
                    </div>

                    <button type="submit"
                            class="btn btn-primary w-full rounded-xl px-6 py-3.5 text-sm font-semibold">
                        <?= esc($submitLabel) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

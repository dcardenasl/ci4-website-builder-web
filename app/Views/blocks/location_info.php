<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$sectionTitle       = $data['section_title'] ?? '';
$sectionDescription = $data['section_description'] ?? '';
$addressLabel       = $data['address_label'] ?? '';
$address            = $data['address'] ?? '';
$phoneLabel         = $data['phone_label'] ?? '';
$phone              = $data['phone'] ?? '';
$hoursLabel         = $data['hours_label'] ?? '';
$hours              = $data['hours'] ?? '';
$mapEmbedUrl        = $config['map_embed_url'] ?? '';
$cssClass           = $config['css_class'] ?? '';

if ($sectionTitle === '' && $address === '' && $phone === '' && $hours === '' && $mapEmbedUrl === '') {
    return;
}
?>
<section class="py-12 sm:py-14 <?= esc($cssClass) ?>">
    <div class="container-base">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)] lg:items-start">
            <div class="space-y-5">
                <?php if ($sectionTitle): ?>
                    <h2 class="section-title text-2xl sm:text-3xl">
                        <?= esc($sectionTitle) ?>
                    </h2>
                <?php endif; ?>
                <?php if ($sectionDescription): ?>
                    <p class="section-copy max-w-xl text-base">
                        <?= esc($sectionDescription) ?>
                    </p>
                <?php endif; ?>

                <div class="space-y-4">
                    <?php if ($address): ?>
                        <div class="border-b border-slate-200 pb-4">
                            <p class="section-eyebrow">
                                <?= esc($addressLabel) ?>
                            </p>
                            <p class="section-copy mt-2 text-sm">
                                <?= esc($address) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($phone): ?>
                        <div class="border-b border-slate-200 pb-4">
                            <p class="section-eyebrow">
                                <?= esc($phoneLabel) ?>
                            </p>
                            <a href="tel:<?= esc(preg_replace('/\s+/', '', $phone)) ?>"
                               class="section-copy mt-2 inline-flex text-sm transition-colors hover:text-primary">
                                <?= esc($phone) ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($hours): ?>
                        <div>
                            <p class="section-eyebrow">
                                <?= esc($hoursLabel) ?>
                            </p>
                            <p class="section-copy mt-2 whitespace-pre-line text-sm">
                                <?= esc($hours) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($mapEmbedUrl): ?>
                <div class="surface-card overflow-hidden">
                    <iframe src="<?= esc($mapEmbedUrl) ?>"
                            title="Mapa de ubicación"
                            width="100%"
                            height="100%"
                            class="h-[22rem] w-full md:h-[28rem]"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

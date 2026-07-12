<?php
/**
 * @var array $block
 * @var array $config
 * @var array $data
 */

$title = esc($data['title'] ?? '');
$description = esc($data['description'] ?? '');
$steps = $data['steps'] ?? [];
$steps = is_array($steps) ? $steps : [];
$cssClass = esc(trim($config['css_class'] ?? ''));
?>

<section id="process" class="py-16 bg-slate-50/50 scroll-mt-16 <?= $cssClass ?>">
    <div class="max-w-5xl mx-auto px-4">
        <?php if ($title !== ''): ?>
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-4 bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">
                    <?= $title ?>
                </h2>
                <?php if ($description !== ''): ?>
                    <p class="text-lg text-slate-600">
                        <?= $description ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($steps === []): ?>
            <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                No hay pasos registrados.
            </div>
        <?php else: ?>
            <div class="relative">
                <!-- Step Connect Line (Desktop only) -->
                <div class="hidden lg:block absolute left-8 right-8 top-12 h-0.5 bg-slate-200 z-0"></div>

                <div class="grid gap-8 grid-cols-1 lg:grid-cols-<?= count($steps) ?> relative z-10">
                    <?php foreach ($steps as $idx => $step): 
                        $stepNum = esc($step['step_number'] ?? ($idx + 1));
                        $sTitle = esc($step['title'] ?? '');
                        $sDesc = esc($step['description'] ?? '');
                        if ($sTitle === '') continue;
                    ?>
                        <div class="flex lg:flex-col gap-5 lg:gap-0 lg:text-center items-start lg:items-center bg-white lg:bg-transparent p-6 lg:p-0 rounded-2xl border border-slate-200/50 lg:border-0 shadow-sm lg:shadow-none">
                            <!-- Circle Indicator -->
                            <div class="shrink-0 flex items-center justify-center w-12 h-12 lg:w-16 lg:h-16 rounded-full border-4 border-slate-50 lg:border-white bg-violet-600 text-white font-extrabold text-lg lg:text-xl shadow-md lg:mb-6">
                                <?= $stepNum ?>
                            </div>
                            
                            <!-- Content -->
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold text-slate-800 tracking-tight mb-2">
                                    <?= $sTitle ?>
                                </h3>
                                <?php if ($sDesc !== ''): ?>
                                    <p class="text-sm text-slate-500 leading-relaxed max-w-xs mx-auto">
                                        <?= $sDesc ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

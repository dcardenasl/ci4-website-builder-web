<?php
/**
 * @var array $block
 * @var array $config
 * @var array $data
 */

$title = esc($data['title'] ?? '');
$description = esc($data['description'] ?? '');
$members = $data['members'] ?? [];
$members = is_array($members) ? $members : [];
$columns = esc($config['columns'] ?? '3');
$cssClass = esc(trim($config['css_class'] ?? ''));

$colClasses = [
    '2' => 'grid-cols-1 sm:grid-cols-2',
    '3' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
    '4' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
];
$colClass = $colClasses[$columns] ?? $colClasses['3'];
?>

<section id="team" class="py-16 bg-white scroll-mt-16 <?= $cssClass ?>">
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

        <?php if ($members === []): ?>
            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-400">
                No hay integrantes del equipo registrados.
            </div>
        <?php else: ?>
            <div class="grid gap-8 <?= $colClass ?>">
                <?php foreach ($members as $m): 
                    $photo = esc($m['photo_url'] ?? '');
                    $name = esc($m['name'] ?? '');
                    $position = esc($m['position'] ?? '');
                    $bio = esc($m['bio'] ?? '');
                    $linkedin = esc($m['linkedin_url'] ?? '');
                    if ($name === '') continue;
                ?>
                    <div class="flex flex-col items-center text-center p-6 rounded-3xl border border-slate-200/50 bg-white hover:border-violet-200 hover:shadow-md transition-all duration-300 group">
                        <!-- Photo Wrapper -->
                        <div class="relative w-28 h-28 rounded-full overflow-hidden bg-slate-50 border border-slate-200 mb-5 group-hover:scale-105 transition-transform duration-300">
                            <?php if ($photo !== ''): ?>
                                <img src="<?= $photo ?>" alt="<?= $name ?>" class="w-full h-full object-cover" loading="lazy" />
                            <?php else: ?>
                                <!-- Initials fallback -->
                                <div class="flex items-center justify-center w-full h-full bg-gradient-to-br from-violet-100 to-violet-50 text-violet-600 font-extrabold text-2xl">
                                    <?= strtoupper(substr($name, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="min-w-0 flex-1 flex flex-col justify-between w-full">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 tracking-tight group-hover:text-violet-600 transition-colors truncate">
                                    <?= $name ?>
                                </h3>
                                <p class="text-xs font-semibold text-violet-600 uppercase tracking-wider mt-1 mb-3">
                                    <?= $position ?>
                                </p>
                                <?php if ($bio !== ''): ?>
                                    <p class="text-sm text-slate-500 line-clamp-3 leading-relaxed mb-4">
                                        <?= $bio ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Social -->
                            <?php if ($linkedin !== ''): ?>
                                <div class="flex justify-center pt-2 border-t border-slate-100 w-full">
                                    <a href="<?= $linkedin ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                        </svg>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

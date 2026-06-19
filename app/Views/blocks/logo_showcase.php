<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$logos = [];
foreach ($block['children'] ?? [] as $child) {
    if (($child['block_key'] ?? '') !== 'logo_item') {
        continue;
    }
    $childData = $child['block_data'] ?? [];
    
    $logos[] = [
        'logo_url' => (string) ($childData['logo_url'] ?? ''),
        'name'     => (string) ($childData['name'] ?? ''),
        'link_url' => (string) ($childData['link_url'] ?? ''),
    ];
}

if ($logos === []) {
    return;
}

$layout = (string) ($config['layout'] ?? 'marquee');
$speed = (string) ($config['speed'] ?? 'normal');
$grayscale = filter_var($config['grayscale'] ?? true, FILTER_VALIDATE_BOOL);
$cssClass = trim((string) ($config['css_class'] ?? ''));

$isMarquee = $layout === 'marquee';

// Speed matching
$duration = '25s';
if ($speed === 'slow') {
    $duration = '40s';
} elseif ($speed === 'fast') {
    $duration = '12s';
}

$logoStyleClass = $grayscale ? 'filter grayscale hover:grayscale-0 opacity-60 hover:opacity-100 transition-all duration-300' : 'opacity-80 hover:opacity-100 transition-all duration-300';
?>

<section class="py-6 overflow-hidden <?= esc($cssClass) ?>">
    <?php if ($isMarquee): ?>
        <style>
            @keyframes marquee {
                0% { transform: translateX(0%); }
                100% { transform: translateX(-50%); }
            }
            .marquee-track {
                display: flex;
                width: max-content;
                animation: marquee <?= $duration ?> linear infinite;
            }
            .marquee-track:hover {
                animation-play-state: paused;
            }
        </style>
        
        <div class="relative w-full overflow-hidden flex items-center py-4 mask-gradient-h">
            <!-- Fade masks for smooth edges -->
            <div class="absolute left-0 top-0 bottom-0 w-16 bg-gradient-to-r from-slate-50 to-transparent pointer-events-none z-10"></div>
            <div class="absolute right-0 top-0 bottom-0 w-16 bg-gradient-to-l from-slate-50 to-transparent pointer-events-none z-10"></div>
            
            <div class="marquee-track flex gap-12 items-center">
                <!-- Double the array to ensure seamless looping -->
                <?php 
                $loopLogos = array_merge($logos, $logos, $logos); 
                foreach ($loopLogos as $logo): 
                ?>
                    <div class="flex-shrink-0 h-10 w-32 flex items-center justify-center">
                        <?php if ($logo['link_url'] !== ''): ?>
                            <a href="<?= esc($logo['link_url']) ?>" target="_blank" rel="noopener noreferrer" class="block">
                        <?php endif; ?>
                        
                        <img 
                            src="<?= esc($logo['logo_url']) ?>" 
                            alt="<?= esc($logo['name']) ?>" 
                            title="<?= esc($logo['name']) ?>"
                            class="max-h-full max-w-full object-contain <?= $logoStyleClass ?>"
                            loading="lazy"
                        />
                        
                        <?php if ($logo['link_url'] !== ''): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Grid Layout -->
        <div class="max-w-6xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-8 items-center justify-items-center py-4">
            <?php foreach ($logos as $logo): ?>
                <div class="h-12 w-32 flex items-center justify-center">
                    <?php if ($logo['link_url'] !== ''): ?>
                        <a href="<?= esc($logo['link_url']) ?>" target="_blank" rel="noopener noreferrer" class="block">
                    <?php endif; ?>
                    
                    <img 
                        src="<?= esc($logo['logo_url']) ?>" 
                        alt="<?= esc($logo['name']) ?>" 
                        title="<?= esc($logo['name']) ?>"
                        class="max-h-full max-w-full object-contain <?= $logoStyleClass ?>"
                        loading="lazy"
                    />
                    
                    <?php if ($logo['link_url'] !== ''): ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

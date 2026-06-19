<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$stats = [];
foreach ($block['children'] ?? [] as $child) {
    if (($child['block_key'] ?? '') !== 'stat_item') {
        continue;
    }
    $childData = $child['block_data'] ?? [];
    
    $stats[] = [
        'number' => (string) ($childData['number'] ?? ''),
        'label'  => (string) ($childData['label'] ?? ''),
        'icon'   => (string) ($childData['icon'] ?? ''),
    ];
}

if ($stats === []) {
    return;
}

$variant = (string) ($config['variant'] ?? 'light');
$cssClass = trim((string) ($config['css_class'] ?? ''));

// Map variants
$sectionClass = 'rounded-3xl py-10 px-6 md:px-12 ';
$numColorClass = 'text-violet-600';
$lblColorClass = 'text-slate-600';
$iconColorClass = 'text-violet-500 bg-violet-50';

if ($variant === 'dark') {
    $sectionClass .= 'bg-slate-900 border border-slate-800 text-white shadow-xl';
    $numColorClass = 'text-violet-400';
    $lblColorClass = 'text-slate-400';
    $iconColorClass = 'text-violet-400 bg-slate-800';
} elseif ($variant === 'primary') {
    $sectionClass .= 'bg-gradient-to-tr from-violet-600 to-indigo-700 text-white shadow-lg shadow-violet-500/20';
    $numColorClass = 'text-amber-300';
    $lblColorClass = 'text-violet-100/90';
    $iconColorClass = 'text-amber-300 bg-violet-800/50';
} else { // light
    $sectionClass .= 'bg-white border border-slate-100 shadow-sm';
}
?>

<section class="py-8 <?= esc($cssClass) ?>">
    <div class="<?= esc($sectionClass) ?>">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-<?= count($stats) === 3 ? '3' : (count($stats) === 2 ? '2' : '4') ?> divide-y sm:divide-y-0 sm:divide-x divide-slate-100/10">
            <?php foreach ($stats as $stat): ?>
                <!-- Remove non-numeric characters to get pure target value for counting animation -->
                <?php 
                $numOnly = (int) preg_replace('/[^0-9]/', '', $stat['number']); 
                $suffix = preg_replace('/[0-9]/', '', $stat['number']);
                ?>
                <div class="flex flex-col items-center text-center p-4 first:pt-0 sm:first:pt-4">
                    <?php if ($stat['icon'] !== ''): ?>
                        <div class="mb-4 h-12 w-12 rounded-2xl flex items-center justify-center <?= esc($iconColorClass) ?>">
                            <!-- Render generic Lucide SVG or standard placeholder if SVG mapping is not loaded client-side -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                    <?php endif; ?>
                    
                    <span 
                        class="text-4xl md:text-5xl font-black tracking-tight <?= esc($numColorClass) ?> mb-2"
                        data-stat-counter
                        data-target-value="<?= esc((string) $numOnly) ?>"
                        data-suffix="<?= esc($suffix) ?>"
                    >
                        <?= esc($stat['number']) ?>
                    </span>
                    
                    <span class="text-sm md:text-base font-semibold tracking-wide uppercase <?= esc($lblColorClass) ?>">
                        <?= esc($stat['label']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Stats Counting Javascript -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const animateCounters = () => {
            const counters = document.querySelectorAll('[data-stat-counter]');
            
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const target = parseInt(el.getAttribute('data-target-value') || '0', 10);
                        const suffix = el.getAttribute('data-suffix') || '';
                        
                        if (target === 0) return;
                        
                        let count = 0;
                        const duration = 1500; // ms
                        const stepTime = Math.max(10, Math.floor(duration / target));
                        
                        const timer = setInterval(() => {
                            count += Math.ceil(target / 50); // fast increment steps
                            if (count >= target) {
                                el.textContent = target + suffix;
                                clearInterval(timer);
                            } else {
                                el.textContent = count + suffix;
                            }
                        }, stepTime);
                        
                        obs.unobserve(el);
                    }
                });
            }, { threshold: 0.2 });
            
            counters.forEach(counter => observer.observe(counter));
        };
        
        animateCounters();
    });
</script>

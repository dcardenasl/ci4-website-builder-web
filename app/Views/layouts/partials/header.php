<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100/80 transition-all duration-200">
    <nav class="container-base py-4 flex justify-between items-center">
        <!-- Logo / Site Title -->
        <a href="<?= base_url() ?>" class="text-xl font-bold tracking-tight text-slate-900 hover:text-primary transition-all duration-200 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-primary animate-pulse"></span>
            <?= esc($settings['site_title'] ?? 'Website') ?>
        </a>

        <!-- Desktop Navigation Links (Hidden on Mobile) -->
        <ul class="hidden md:flex gap-1.5 items-center">
            <?php foreach (($menu['items'] ?? []) as $item): ?>
                <li class="relative group">
                    <a href="<?= esc($item['custom_url'] ?? '#') ?>" 
                       class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-slate-600 hover:text-primary hover:bg-slate-50/80 rounded-lg transition-all duration-200">
                        <?= esc($item['label'] ?? '') ?>
                        <?php if (!empty($item['children'])): ?>
                            <svg class="w-3.5 h-3.5 opacity-60 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        <?php endif; ?>
                    </a>

                    <!-- Dropdown Menu -->
                    <?php if (!empty($item['children'])): ?>
                        <div class="absolute left-0 mt-1.5 w-52 bg-white/95 backdrop-blur-md border border-slate-100 rounded-xl shadow-xl shadow-slate-100/50 opacity-0 invisible group-hover:opacity-100 group-hover:visible translate-y-1 group-hover:translate-y-0 transition-all duration-300 py-1.5 z-50">
                            <?php foreach ($item['children'] as $subitem): ?>
                                <a href="<?= esc($subitem['custom_url'] ?? '#') ?>" 
                                   class="block px-4 py-2 text-sm font-medium text-slate-600 hover:text-primary hover:bg-slate-50 transition-colors">
                                    <?= esc($subitem['label'] ?? '') ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Mobile Menu Toggle Button (Visible on Mobile Only) -->
        <button id="mobile-menu-toggle" class="md:hidden p-2.5 text-slate-600 hover:text-primary hover:bg-slate-50 rounded-lg transition-all focus:outline-none" aria-label="Toggle Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path id="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </nav>

    <!-- Mobile Navigation Drawer (Hidden on Desktop) -->
    <div id="mobile-drawer" class="fixed left-0 top-[61px] w-full h-[calc(100vh-61px)] z-40 bg-white border-t border-slate-100 md:hidden opacity-0 pointer-events-none translate-y-4 transition duration-200 ease-in-out flex flex-col justify-between overflow-y-auto">
        <div class="px-6 py-6 space-y-6">
            <ul class="space-y-4">
                <?php foreach (($menu['items'] ?? []) as $item): ?>
                    <li class="border-b border-slate-100/50 pb-3 last:border-0 last:pb-0">
                        <?php if (!empty($item['children'])): ?>
                            <!-- Clickable Row for Items with Children -->
                            <div class="mobile-submenu-row flex justify-between items-center cursor-pointer py-1" data-target="submenu-<?= $item['id'] ?>">
                                <span class="text-base font-semibold text-slate-800 hover:text-primary transition-colors">
                                    <?= esc($item['label'] ?? '') ?>
                                </span>
                                <button class="text-slate-400 hover:text-primary focus:outline-none pointer-events-none">
                                    <svg class="w-4.5 h-4.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        <?php else: ?>
                            <!-- Standard Link for Leaf Items -->
                            <div class="flex justify-between items-center py-1">
                                <a href="<?= esc($item['custom_url'] ?? '#') ?>" class="text-base font-semibold text-slate-800 hover:text-primary transition-colors w-full">
                                    <?= esc($item['label'] ?? '') ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['children'])): ?>
                            <ul id="submenu-<?= $item['id'] ?>" class="hidden mt-2 pl-4 border-l border-slate-100 space-y-3">
                                <?php foreach ($item['children'] as $subitem): ?>
                                    <li>
                                        <a href="<?= esc($subitem['custom_url'] ?? '#') ?>" class="block text-sm font-medium text-slate-500 hover:text-primary transition-colors">
                                            <?= esc($subitem['label'] ?? '') ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <!-- Mobile Drawer Footer info -->
        <div class="bg-slate-50 p-6 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-400">
                <?= esc($settings['site_tagline'] ?? '') ?>
            </p>
        </div>
    </div>
</header>

<script>
(() => {
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const drawer = document.getElementById('mobile-drawer');
    const iconPath = document.getElementById('menu-icon-path');
    
    if (!toggleBtn || !drawer) return;

    let isOpen = false;

    toggleBtn.addEventListener('click', () => {
        isOpen = !isOpen;
        if (isOpen) {
            drawer.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
            drawer.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
            if (iconPath) iconPath.setAttribute('d', 'M6 18L18 6M6 6l12 12');
            document.body.style.overflow = 'hidden';
        } else {
            drawer.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
            drawer.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
            if (iconPath) iconPath.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
            document.body.style.overflow = '';
        }
    });

    const toggleSubmenu = (row) => {
        const targetId = row.getAttribute('data-target');
        const submenu = document.getElementById(targetId);
        const svg = row.querySelector('svg');
        
        if (submenu) {
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                if (svg) svg.classList.add('rotate-180');
            } else {
                submenu.classList.add('hidden');
                if (svg) svg.classList.remove('rotate-180');
            }
        }
    };

    const submenuRows = document.querySelectorAll('.mobile-submenu-row');
    submenuRows.forEach(row => {
        // Instant response on mobile touch
        row.addEventListener('touchstart', (e) => {
            e.preventDefault(); // Prevents the 300ms click emulation delay and double toggling
            toggleSubmenu(row);
        }, { passive: false });

        // Fallback/standard click for desktop and emulators
        row.addEventListener('click', (e) => {
            // Only toggle if touchstart did not handle it
            if (e.defaultPrevented) return;
            toggleSubmenu(row);
        });
    });
})();
</script>

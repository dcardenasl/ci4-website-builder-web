(function () {
  const setDrawerOpen = (toggleBtn, drawer, iconPath, open) => {
    if (!toggleBtn || !drawer) return;

    drawer.classList.toggle('opacity-0', !open);
    drawer.classList.toggle('pointer-events-none', !open);
    drawer.classList.toggle('translate-y-4', !open);
    drawer.classList.toggle('opacity-100', open);
    drawer.classList.toggle('pointer-events-auto', open);
    drawer.classList.toggle('translate-y-0', open);

    toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (iconPath) {
      iconPath.setAttribute('d', open ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16');
    }

    document.body.style.overflow = open ? 'hidden' : '';
  };

  const initMobileDrawer = () => {
    const toggleBtn = document.querySelector('[data-mobile-menu-toggle]');
    const drawer = document.querySelector('[data-mobile-drawer]');
    const iconPath = document.querySelector('[data-mobile-menu-icon]');

    if (!toggleBtn || !drawer) return;

    let isOpen = false;

    toggleBtn.addEventListener('click', () => {
      isOpen = !isOpen;
      setDrawerOpen(toggleBtn, drawer, iconPath, isOpen);
    });

    const submenuRows = drawer.querySelectorAll('.mobile-submenu-row');
    submenuRows.forEach((row) => {
      row.addEventListener('click', () => {
        const targetId = row.getAttribute('data-target');
        if (!targetId) return;

        const submenu = document.getElementById(targetId);
        const svg = row.querySelector('svg');
        if (!submenu) return;

        const willOpen = submenu.classList.contains('hidden');
        submenu.classList.toggle('hidden', !willOpen);
        if (svg) {
          svg.classList.toggle('rotate-180', willOpen);
        }
      });
    });

    drawer.querySelectorAll('a[href]').forEach((link) => {
      link.addEventListener('click', () => {
        if (!isOpen) return;
        isOpen = false;
        setDrawerOpen(toggleBtn, drawer, iconPath, false);
      });
    });
  };

  const parseSlides = (root) => {
    try {
      const slides = JSON.parse(root.dataset.slides || '[]');
      return Array.isArray(slides) ? slides : [];
    } catch (error) {
      return [];
    }
  };

  const initHeroCarousel = (root) => {
    const slides = parseSlides(root);
    if (!slides.length) return;

    const image = root.querySelector('[data-hero-image]');
    const link = root.querySelector('[data-hero-link]');
    const captionTitles = Array.from(root.querySelectorAll('[data-hero-caption-title]'));
    const captionSubtitles = Array.from(root.querySelectorAll('[data-hero-caption-subtitle]'));
    const captionCtas = Array.from(root.querySelectorAll('[data-hero-caption-cta]'));
    const prev = root.querySelector('[data-hero-prev]');
    const next = root.querySelector('[data-hero-next]');
    const dots = Array.from(root.querySelectorAll('[data-hero-dot]'));
    const autoplayEnabled = root.dataset.autoplay !== '0';
    const slideDuration = Math.max(1000, Number(root.dataset.interval || 6000));
    const hoverTarget = image || root;

    const dotFills = dots.map((dot) => {
      let fill = dot.querySelector('[data-hero-dot-fill]');
      if (!fill) {
        fill = document.createElement('span');
        fill.setAttribute('data-hero-dot-fill', '');
        fill.className = 'block h-full w-full bg-slate-900';
        dot.appendChild(fill);
      }
      return fill;
    });

    let current = 0;
    let timer = null;
    let progressTimer = null;
    let startedAt = 0;
    let remainingMs = slideDuration;
    let paused = false;

    const clearProgress = () => {
      dotFills.forEach((fill) => {
        fill.style.transform = 'scaleX(0)';
      });
    };

    const setActiveDot = () => {
      dots.forEach((dot, dotIndex) => {
        const active = dotIndex === current;
        dot.classList.toggle('bg-slate-100', active);
        dot.classList.toggle('bg-slate-200', !active);
        dot.style.width = active ? '1rem' : '0.5rem';
        dot.style.height = '0.5rem';
        dot.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    };

    const updateProgress = () => {
      const activeFill = dotFills[current];
      if (!activeFill || !startedAt) return;

      const elapsed = Math.min(slideDuration, Math.max(0, Date.now() - startedAt));
      const ratio = slideDuration > 0 ? elapsed / slideDuration : 1;
      activeFill.style.transform = `scaleX(${Math.max(0, Math.min(1, ratio))})`;
    };

    const stopProgress = () => {
      if (!autoplayEnabled || paused) return;
      const elapsed = Math.max(0, Date.now() - startedAt);
      remainingMs = Math.max(0, slideDuration - elapsed);
      window.clearTimeout(timer);
      timer = null;
      if (progressTimer) {
        window.clearInterval(progressTimer);
        progressTimer = null;
      }
      updateProgress();
      paused = true;
    };

    const scheduleNext = (delayMs = slideDuration) => {
      if (slides.length < 2 || !autoplayEnabled) return;

      window.clearTimeout(timer);
      timer = window.setTimeout(() => {
        current = (current + 1) % slides.length;
        remainingMs = slideDuration;
        paused = false;
        render();
        clearProgress();
        startedAt = Date.now();
        updateProgress();
        scheduleNext(slideDuration);
      }, delayMs);
      startedAt = Date.now();
      remainingMs = delayMs;
    };

    const resumeProgress = () => {
      if (!autoplayEnabled || !paused || !slides.length) return;
      paused = false;
      startedAt = Date.now() - (slideDuration - remainingMs);
      if (progressTimer) {
        window.clearInterval(progressTimer);
      }
      progressTimer = window.setInterval(updateProgress, 50);
      updateProgress();
      scheduleNext(remainingMs);
    };

    const start = () => {
      if (slides.length < 2 || !autoplayEnabled) {
        clearProgress();
        setActiveDot();
        return;
      }

      stop();
      paused = false;
      remainingMs = slideDuration;
      clearProgress();
      startedAt = Date.now();
      progressTimer = window.setInterval(updateProgress, 50);
      updateProgress();
      scheduleNext(slideDuration);
    };

    const stop = () => {
      if (timer) {
        window.clearTimeout(timer);
        timer = null;
      }
      if (progressTimer) {
        window.clearInterval(progressTimer);
        progressTimer = null;
      }
      paused = false;
    };

    const render = () => {
      const slide = slides[current];
      if (!slide) return;

      if (image) {
        image.src = slide.image_url || '';
        image.alt = slide.image_alt_text || slide.heading || '';
      }
      if (link) {
        link.href = slide.cta_url || '#';
        link.setAttribute('aria-label', slide.heading || '');
      }
      captionTitles.forEach((node) => {
        node.textContent = slide.heading || '';
        node.hidden = !slide.heading;
      });
      captionSubtitles.forEach((node) => {
        node.textContent = slide.subtitle || '';
        node.hidden = !slide.subtitle;
      });
      captionCtas.forEach((node) => {
        node.textContent = slide.cta_label || '';
        node.hidden = !slide.cta_label;
      });

      setActiveDot();
    };

    const goToSlide = (index) => {
      current = index;
      remainingMs = slideDuration;
      paused = false;
      render();
      start();
    };

    if (prev) {
      prev.addEventListener('click', () => {
        const nextIndex = (current - 1 + slides.length) % slides.length;
        goToSlide(nextIndex);
      });
    }

    if (next) {
      next.addEventListener('click', () => {
        const nextIndex = (current + 1) % slides.length;
        goToSlide(nextIndex);
      });
    }

    dots.forEach((dot, dotIndex) => {
      dot.addEventListener('click', () => {
        goToSlide(dotIndex);
      });
    });

    if (hoverTarget) {
      hoverTarget.addEventListener('mouseenter', stopProgress, { passive: true });
      hoverTarget.addEventListener('mouseleave', resumeProgress, { passive: true });
    }

    start();
    render();
    clearProgress();
    updateProgress();
  };

  document.querySelectorAll('[data-hero-carousel]').forEach(initHeroCarousel);
  initMobileDrawer();
})();

/**
 * Public site entry point.
 *
 * Source of truth for all site JavaScript. The committed artifact
 * public/assets/js/site.js is generated from here with esbuild:
 *   npm run build:js
 */
import { initMobileDrawer } from './components/mobileDrawer.js';
import { initHeroCarousels } from './components/heroCarousel.js';
import { initCardsSliders } from './components/cardsSlider.js';
import { initMetricsCounters } from './components/metricsCounter.js';
import { initVideoPlayers } from './components/videoPlayer.js';
import { initCollectionFilters } from './components/collectionFilters.js';
import { initShareButtons } from './components/shareButtons.js';

const readCookie = (name) => {
  const prefix = `${name}=`;
  const match = document.cookie.split('; ').find((cookie) => cookie.startsWith(prefix));
  return match ? decodeURIComponent(match.slice(prefix.length)) : '';
};

const initCsrfTokens = () => {
  document.querySelectorAll('[data-csrf-token]').forEach((field) => {
    const cookieName = field.getAttribute('data-csrf-cookie');
    if (cookieName) {
      field.value = readCookie(cookieName);
    }
  });
};

const boot = () => {
  initCsrfTokens();
  initMobileDrawer();
  initHeroCarousels();
  initCardsSliders();
  initMetricsCounters();
  initVideoPlayers();
  initCollectionFilters();
  initShareButtons();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot);
} else {
  boot();
}

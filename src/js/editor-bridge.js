/* global Element, HTMLScriptElement */

/**
 * Editor canvas bridge — the iframe half.
 *
 * The preview is a separate trust boundary. Messages are accepted only from
 * the configured panel window, exact origin, protocol and per-iframe channel.
 */
(() => {
  'use strict';

  const script = document.currentScript;
  if (!(script instanceof HTMLScriptElement)) return;

  const protocol = 1;
  const channel = script.dataset.channel || '';
  const panelOrigin = script.dataset.panelOrigin || '';
  const parentWindow = window.parent;
  if (!channel || !panelOrigin || !parentWindow || parentWindow === window) return;

  const validRef = (value) => typeof value === 'string' && value.length > 0 && value.length <= 128;
  const payloadObject = (value) => value !== null && typeof value === 'object' && !Array.isArray(value);
  const send = (type, payload = {}) => {
    parentWindow.postMessage({ protocol, channel, type, payload }, panelOrigin);
  };
  const blockAt = (target) => target instanceof Element ? target.closest('[data-block-instance]') : null;
  const refOf = (block) => block?.getAttribute('data-block-instance') || null;
  const findBlock = (ref) => {
    if (!validRef(ref)) return null;
    return [...document.querySelectorAll('[data-block-instance]')]
      .find((node) => node.getAttribute('data-block-instance') === ref) || null;
  };
  const select = (ref) => {
    document.querySelectorAll('[data-block-instance]').forEach((node) => {
      node.classList.toggle('is-editor-selected', node.getAttribute('data-block-instance') === ref);
    });
  };

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) return;
    event.preventDefault();
    const block = blockAt(event.target);
    const ref = refOf(block);
    if (!validRef(ref)) return;
    select(ref);
    send('block:select', { ref });
  }, true);

  document.addEventListener('submit', (event) => event.preventDefault(), true);

  let hovered = null;
  document.addEventListener('mouseover', (event) => {
    const ref = refOf(blockAt(event.target));
    if (ref === hovered) return;
    hovered = ref;
    send('block:hover', { ref });
  }, true);

  let appliedSequence = 0;
  window.addEventListener('message', (event) => {
    if (event.origin !== panelOrigin || event.source !== parentWindow) return;
    const message = event.data;
    if (!payloadObject(message) || message.protocol !== protocol || message.channel !== channel
      || typeof message.type !== 'string' || !payloadObject(message.payload)) return;

    const payload = message.payload;
    if (message.type === 'canvas:select' && validRef(payload.ref)) {
      select(payload.ref);
      return;
    }
    if (message.type === 'canvas:scrollTo' && validRef(payload.ref)) {
      findBlock(payload.ref)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    if (message.type !== 'canvas:replaceBlock' || !validRef(payload.ref)
      || typeof payload.html !== 'string' || payload.html.length > 524288) return;

    const sequence = Number(payload.sequence);
    if (!Number.isSafeInteger(sequence) || sequence <= appliedSequence) return;
    const target = findBlock(payload.ref);
    if (!target) return;
    appliedSequence = sequence;
    target.outerHTML = payload.html;
    select(payload.ref);
  });

  send('doc:ready', {
    blocks: [...document.querySelectorAll('[data-block-instance]')].map((node) => ({
      ref: node.getAttribute('data-block-instance'),
      fallback: node.getAttribute('data-block-fallback') === '1',
    })),
  });
})();

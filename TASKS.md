# TASKS — ci4-website-builder-web

> Trabajo abierto de este repositorio. Plan cross-repo:
> [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md).

## 🔴 En progreso

*(vacío)*

## 🟡 Próximo

- [ ] **CNV-007-W1 — Anotador de bloques.** Extensión explícita del `BlockRenderer`, solo en modo
      editor, preservando exactamente el HTML público.
- [ ] **CNV-007-W2 — Preview firmado.** `POST /{locale}/_editor/preview` con scopes, owner/locale,
      throttle, `no-store`, `noindex`, errores seguros y delegación al Domain.
- [ ] **CNV-007-W3 — Embebido seguro.** `frame-ancestors` exacto por ruta y retiro de
      `X-Frame-Options: DENY` solo para preview; página pública permanece no embebible.
- [ ] **CNV-007-W4 — CORS y bridge.** Allowlist exacta, `event.source`/canal/secuencia validados y
      bridge bundleado localmente, sin CDN.
- [ ] **CNV-007-F6 — Smoke real.** Verificar Panel ↔ Web cross-origin, selección, scope, locale,
      renovación de token, CSP/CORS y no regresión pública.
- [ ] **CNV-007-F9 — Autorización por recurso.** Solo en la fase final.

## 🏗️ Contratos

- Controllers delgados y lógica en servicios; `WebApiClientInterface` mantiene envelopes y fallback
  stale solo en transporte/5xx, nunca en 4xx.
- CSRF de doble cookie en páginas cacheables; `composer quality` y `npm run build:all` para cerrar.

## 🔧 Referencias

- Arquitectura/CSRF: [`CLAUDE.md`](CLAUDE.md)
- Histórico global: [`../TASKS.md`](../TASKS.md)

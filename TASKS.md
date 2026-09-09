# TASKS — ci4-website-builder-web

> Fuente de verdad para trabajo abierto en este repositorio.
> Seguimiento global: [`../TASKS.md`](../TASKS.md).

## 🔴 En progreso

### Remediación de huecos profundos (parte Web)

> Plan completo: [`../docs/plans/2026-08-25-plan-remediacion-huecos-profundos.md`](../docs/plans/2026-08-25-plan-remediacion-huecos-profundos.md).
> Auditoría origen: [`../docs/audits/2026-08-25-auditoria-profunda-backport-git-history.md`](../docs/audits/2026-08-25-auditoria-profunda-backport-git-history.md).
> Tracker cross-repo: [`../TASKS.md`](../TASKS.md).

## 🟡 Próximo

### CNV-007 — Editor visual (canvas): port del sitio público

> Verificación de arranque en
> [`../ci4-website-suite/docs/plan/2026-09-09-editor-visual-canvas-port-f7.md`](../ci4-website-suite/docs/plan/2026-09-09-editor-visual-canvas-port-f7.md).
> `BlockRenderer` difiere del de la suite en 155 líneas: se reaplica el cambio, no se sobrescribe.

- [ ] **CNV-007-W1 — Anotador de bloques.** Punto de extensión en el recursivo de `BlockRenderer`
      que envuelve cada bloque solo en modo editor; el HTML público no cambia.
- [ ] **CNV-007-W2 — Endpoint de preview firmado.** `POST /{locale}/_editor/preview`, con verificación
      `PreviewToken`, throttle, `no-store` y `noindex`, y los scopes documento/bloque.
- [ ] **CNV-007-W3 — Embebido.** Acotar `frame-ancestors` a esa ruta **y retirar
      `X-Frame-Options: DENY`** en ella: este repo es más estricto que la suite y `DENY` bloquea
      incluso lo que la CSP permita. `'none'` junto a otra fuente anula la directiva entera.
- [ ] **CNV-007-W4 — CORS y bridge.** Orígenes exactos para el fetch de scope bloque, sin comodín,
      y `editor-bridge.js` bundleado (sin CDN).
## ⚪ Backlog

*(vacío)*

## 🏗️ Contratos de arquitectura

- **Controllers delgados:** llaman a `Config\Services`; lógica de resolución vive en servicios.
- **`PageController::resolve()`:** orden de resolución — prefijo/índice de colección, entrada de
  colección, página CMS, redirect, 404.
- **`WebApiClientInterface`:** seam de test para el acceso a Domain; envelopes normalizados
  `{ok, status, data, meta, messages}`; cache keys `web_api_v{N}_{scope}_{md5}`, stale
  `web_api_stale_v{N}_{scope}_{md5}`; fallback a stale solo en transporte fallido (`status 0`) o
  `5xx` — nunca en `4xx`.
- **CSRF en sitio full-page-cacheado:** nunca `csrf_field()`/`csrf_hash()` en una vista servible
  desde cache; usar el patrón de doble-cookie documentado en `CLAUDE.md`.
- **Calidad:** `composer quality` (PHPStan nivel 8 + `phpstan-baseline.neon` decreciente) y
  `npm run build:all` antes de cerrar una tarea.

## 🔧 Referencias

- Tracker global: [`../TASKS.md`](../TASKS.md)
- Arquitectura y CSRF: [`CLAUDE.md`](CLAUDE.md)

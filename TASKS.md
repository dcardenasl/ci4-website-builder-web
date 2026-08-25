# TASKS — ci4-website-builder-web

> Fuente de verdad para trabajo abierto en este repositorio.
> Seguimiento global: [`../TASKS.md`](../TASKS.md).

## 🔴 En progreso

### Remediación de huecos profundos (parte Web)

> Plan completo: [`../docs/plans/2026-08-25-plan-remediacion-huecos-profundos.md`](../docs/plans/2026-08-25-plan-remediacion-huecos-profundos.md).
> Auditoría origen: [`../docs/audits/2026-08-25-auditoria-profunda-backport-git-history.md`](../docs/audits/2026-08-25-auditoria-profunda-backport-git-history.md).
> Tracker cross-repo: [`../TASKS.md`](../TASKS.md).

## 🟡 Próximo
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

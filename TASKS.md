# TASKS — ci4-website-builder-web

> Fuente de verdad para trabajo abierto en este repositorio.
> Seguimiento global: [`../TASKS.md`](../TASKS.md).

## 🔴 En progreso

### Backport de mejoras de Teatro Museo (parte Web)

> Plan completo (contexto, decisiones de alcance, todas las fases, todos los repos):
> [`../docs/plans/2026-08-24-plan-backport-teatromuseo.md`](../docs/plans/2026-08-24-plan-backport-teatromuseo.md).
> Tracker cross-repo: [`../TASKS.md`](../TASKS.md).

## 🟡 Próximo

### Backport de mejoras de Teatro Museo — fases posteriores (parte Web)

- [ ] **BACKPORT-02-web — Fase 2:** ninguna acción directa (el BFF es consumido opcionalmente por
      el sitio público solo si el sitio adopta el patrón de agregación); revisar si aplica al
      momento de ejecutar. Ver plan §Fase 2.
- [ ] **BACKPORT-03-web — Fase 3:** consumir el patrón CMS `page_type` → inyección de dominio
      externo una vez formalizado en domain, si el sitio público expone contenido de un segundo
      dominio. Ver plan §Fase 3.
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

# TASKS — ci4-website-builder-web

> Fuente de verdad para trabajo abierto en este repositorio.
> Seguimiento global: [`../TASKS.md`](../TASKS.md).

## 🔴 En progreso

### Backport de mejoras de Teatro Museo (parte Web)

> Plan completo (contexto, decisiones de alcance, todas las fases, todos los repos):
> [`../docs/plans/2026-08-24-plan-backport-teatromuseo.md`](../docs/plans/2026-08-24-plan-backport-teatromuseo.md).
> Tracker cross-repo: [`../TASKS.md`](../TASKS.md).

- [x] **BACKPORT-CVE-web:** bump `codeigniter4/framework` → v4.7.4 (CVEs críticos/altos de SQLi,
      path traversal y header spoofing). Verificado (ninguno de los comportamientos con CVE se
      ejercita en este código); PHPStan/PHPUnit/CS-Fixer en verde; **pendiente de commit**. Ver
      plan §Remediación de CVEs.
- [ ] **BACKPORT-01-web — Fase 1:** ninguna acción específica identificada para este repo en esta
      fase — confirmar contra el plan al momento de ejecutar.

## 🟡 Próximo

### Backport de mejoras de Teatro Museo — fases posteriores (parte Web)

- [ ] **BACKPORT-02-web — Fase 2:** ninguna acción directa (el BFF es consumido opcionalmente por
      el sitio público solo si el sitio adopta el patrón de agregación); revisar si aplica al
      momento de ejecutar. Ver plan §Fase 2.
- [ ] **BACKPORT-03-web — Fase 3:** consumir el patrón CMS `page_type` → inyección de dominio
      externo una vez formalizado en domain, si el sitio público expone contenido de un segundo
      dominio. Ver plan §Fase 3.
- [ ] **BACKPORT-04-web — Fase 4:** endpoints CMS compuestos (`layout`, `page-bootstrap/{path}`),
      entrega snapshot-first (`pageDeliveryMode=snapshot`, `cache:warmup --strict`), patrón CSRF
      de doble-cookie (`CsrfCookieFilter` después de `pagecache`), fix de Tailwind v4
      `@source inline()` para clases `bg-*` dinámicas. Ver plan §Fase 4.

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

# TASKS_ARCHIVE — ci4-website-builder-web

> Historial de tareas completadas. Movido desde TASKS.md para mantener el tracker activo liviano.

---

## ✅ Backport de mejoras de Teatro Museo — CVE (2026-08-25)

- **BACKPORT-CVE-web** — `codeigniter4/framework` 4.7.4 para remediar los CVEs de SQLi,
  path-traversal y spoofing de headers; verificado con `composer quality`.

## ✅ Backport de mejoras de Teatro Museo — Fase 1 (2026-08-25)

- **BACKPORT-01-web** — confirmado contra el plan y el código real que no hay acción específica
  para Web en esta fase. `createFetchQueue()` solo figura en el changelog de Teatro Museo, no en
  su fuente disponible ni en este repo. Verificado con `composer quality`, ESLint y `npm run build:all`.

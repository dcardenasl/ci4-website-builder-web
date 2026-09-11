# TASKS — ci4-website-builder-web

> Trabajo abierto de este repositorio. Plan cross-repo:
> [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md).

## 🔴 En progreso

*(vacío)*

## 🟡 Próximo

*(vacío; la autorización por recurso es Domain-owned y no se duplica en Web)*

## ✅ Cerrado con evidencia

- **CNV-007-W1..W4.** Commits `ce8bd20`, `8eb9c90`, `b106f7f` y `548d185`; anotaciones,
  preview firmado, CSP/iframe, CORS exacto y bridge local con contratos de seguridad.
- **CNV-007-F6 — Smoke real.** Web `POST /es/_editor/preview` y `/en/_editor/preview` `200`,
  bridge servido, CSP exacta, POST CORS completo desde Admin y no regresión de `/es/home`.
- **F6 configuración.** `.env.example` fija `EDITOR_PANEL_ORIGIN=http://localhost:8182`; el launcher
  valida el secreto HMAC compartido y el origen del panel antes de levantar el stack.
- **CNV-007-P2-E2E — Health gate portable.** `PlatformHealthE2ETest` acepta `PLATFORM_E2E_HOST`
  para ejecutar el gate con `localhost` o `127.0.0.1` según el runtime, sin duplicar casos.
- **CNV-007-F9 — Reconciliación de alcance.** No se añade ACL local: Domain aplica el alcance por
  recurso y Web solo verifica el preview firmado y sus contratos de origen. Evidencia Domain:
  `729aa89`; cualquier cambio futuro requiere un contrato de consumidor explícito.

## 🏗️ Contratos

- Controllers delgados y lógica en servicios; `WebApiClientInterface` mantiene envelopes y fallback
  stale solo en transporte/5xx, nunca en 4xx.
- CSRF de doble cookie en páginas cacheables; `composer quality` y `npm run build:all` para cerrar.

## 🔧 Referencias

- Arquitectura/CSRF: [`CLAUDE.md`](CLAUDE.md)
- Histórico global: [`../TASKS.md`](../TASKS.md)

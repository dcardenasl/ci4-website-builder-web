# TASKS — ci4-website-builder-web

> Trabajo abierto de este repositorio. Plan cross-repo:
> [`../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md`](../docs/plans/2026-09-11-plan-nivelacion-stack-modular-suite.md).

## 🔴 En progreso

*(vacío)*

## 🟡 Próximo

- [ ] **CNV-007-F9 — Autorización por recurso.** Solo en la fase final.

## ✅ Cerrado con evidencia

- **CNV-007-W1..W4.** Commits `ce8bd20`, `8eb9c90`, `b106f7f` y `548d185`; anotaciones,
  preview firmado, CSP/iframe, CORS exacto y bridge local con contratos de seguridad.
- **CNV-007-F6 — Smoke real.** Web `POST /es/_editor/preview` y `/en/_editor/preview` `200`,
  bridge servido, CSP exacta, POST CORS completo desde Admin y no regresión de `/es/home`.
- **F6 configuración.** `.env.example` fija `EDITOR_PANEL_ORIGIN=http://localhost:8182`; el launcher
  valida el secreto HMAC compartido y el origen del panel antes de levantar el stack.

## 🏗️ Contratos

- Controllers delgados y lógica en servicios; `WebApiClientInterface` mantiene envelopes y fallback
  stale solo en transporte/5xx, nunca en 4xx.
- CSRF de doble cookie en páginas cacheables; `composer quality` y `npm run build:all` para cerrar.

## 🔧 Referencias

- Arquitectura/CSRF: [`CLAUDE.md`](CLAUDE.md)
- Histórico global: [`../TASKS.md`](../TASKS.md)

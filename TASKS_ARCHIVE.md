# TASKS_ARCHIVE — ci4-website-builder-web

> Historial de tareas completadas. Movido desde TASKS.md para mantener el tracker activo liviano.

Última actualización: 2026-08-25.

## ✅ Remediación de huecos profundos — Fase 3 (2026-08-25)

- **GAP-03-web** — cerrados los 9 ítems aplicables: claves de caché con query string, índice de
  invalidación HTML, locale estático, tracking asíncrono con `analytics:flush`, sitemap paginado,
  sesión condicional, single-flight, `X-Request-ID` y timeouts cURL. Commits `5fd95f5`, `e614da2`,
  `79dec29`, `ddeec81`, `b51f850`, `4d9c50f`, `4071843`, `9bccb91`, `0944c88`; `composer quality`
  y `npm run build:all` verdes.

## ✅ Remediación de huecos profundos — Fase 0 (2026-08-25)

- **GAP-00-web** — CSP nativo estricto con nonces para scripts/estilos inline, orden de filtros
  corregido, regresión feature y smoke en navegador real con `CSPEnabled=true` sin bloqueos.
  Commit `a600ae9`; `composer quality`, build/lint JS y `composer cs-check` verificados.

## ✅ Backport de mejoras de Teatro Museo — Fase 5 (2026-08-25)

- **BACKPORT-05-web** — documentación de entrega compuesta, snapshot, CSRF/cache, CSP nonce y
  defaults de runtime; se eliminó una referencia histórica específica de Teatro Museo del
  documento de implementación y se verificó el build frontend.

## ✅ Backport de mejoras de Teatro Museo — Fase 4 (2026-08-25)

- **BACKPORT-04-web** — bootstrap público compuesto con fallback, round-trip budget, snapshot
  warmup con lock, CSRF doble-cookie posterior a `pagecache`, nonces CSP para estilos scoped,
  invalidación de caches compuestos y sparse-fieldset consumption en Domain. Commits `ffe8a3c`,
  `970a1b3`, `b6dbfd4`; `composer quality`, PHPUnit 195/621 (5 skips), ESLint y build frontend
  verdes. Tailwind `@source inline(...)` ya existía y quedó documentado como falso positivo.

---

## ✅ Backport de mejoras de Teatro Museo — CVE (2026-08-25)

- **BACKPORT-CVE-web** — `codeigniter4/framework` 4.7.4 para remediar los CVEs de SQLi,
  path-traversal y spoofing de headers; verificado con `composer quality`.

## ✅ Backport de mejoras de Teatro Museo — Fase 1 (2026-08-25)

- **BACKPORT-01-web** — confirmado contra el plan y el código real que no hay acción específica
  para Web en esta fase. `createFetchQueue()` solo figura en el changelog de Teatro Museo, no en
  su fuente disponible ni en este repo. Verificado con `composer quality`, ESLint y `npm run build:all`.

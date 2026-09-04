# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

drakAI is a Laravel + Vue 3 SPA that lets a user upload/paste a large document and get an AI-generated summary back (via the Gemini API — not yet integrated). No user accounts, no database: every request is stateless, processed against Gemini and returned.

## Reglas de trabajo

- No intentes comprobar vistas ni nada de forma visual/manual (screenshots, Playwright, capturas, abrir el navegador, etc.). Verifica con los medios no visuales disponibles: build (`npm run build`), logs de contenedores, `curl`, tests, linters, etc. Si algo requiere validación visual, pídesela al usuario en vez de intentarla tú mismo.

## Commands

Local dev runs through Laravel Sail (Docker). Only two services exist: `laravel.test` (PHP 8.3) and `vite` (frontend dev server) — no database, no queue worker, no mail service.

```bash
./vendor/bin/sail up -d           # start (backend on :8000, Vite on :5173)
./vendor/bin/sail down             # stop
./vendor/bin/sail artisan ...      # run artisan inside the container
./vendor/bin/sail composer ...     # run composer inside the container
./vendor/bin/sail test             # phpunit (config:clear + php artisan test)
```

If PHP/Composer aren't installed on the host, run composer through Docker directly (no `sail` binary needed for this one-off step):

```bash
docker run --rm -v "$PWD":/app -w /app -u "$(id -u):$(id -g)" composer:2 composer install
```

Composer's `config.platform.php` is pinned to `8.3.33` in `composer.json` — this must match whatever PHP version `vendor/laravel/sail/runtimes/<version>` actually ships, otherwise `vendor/composer/platform_check.php` rejects boot inside the container even though `composer install` succeeded outside it (mismatched PHP between the machine that ran Composer and the Sail runtime). If the Sail runtime version in `docker-compose.yml` (`./vendor/laravel/sail/runtimes/8.3`) ever changes, update this platform pin to match and re-run `composer update`.

Frontend (also runnable outside Docker for quick iteration, since Vite/Node don't need PHP):

```bash
npm install
npm run dev                        # vite dev server
npm run build                      # production build
npm run build:icons                # regenerate resources/js/plugins/iconify/icons.css (needs @iconify-json/* + @iconify/tools, not installed by default — see that file's header)
```

Backend tests:

```bash
./vendor/bin/sail test
# or a single test:
./vendor/bin/sail artisan test --filter=test_the_application_returns_a_successful_response
```

There is no frontend test suite yet.

## Architecture

**Backend is a thin shell.** `routes/web.php` has a single catch-all (`Route::view('/{any}', 'application')->where('any', '^(?!api).*$')`) that serves `resources/views/application.blade.php`, which just mounts the Vue app (`@vite(['resources/js/main.js'])` + `<div id="app">`). All actual page routing happens client-side in `resources/js/plugins/router/routes.js` (vue-router). `routes/web.php` is intentionally minimal; a Gemini-backed `/api/*` route group is where the actual summarization endpoint(s) will go once built (the `(?!api)` negative lookahead in the catch-all already carves out that space).

**No database.** `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` in `.env` — nothing here needs Postgres/MySQL/Redis. Don't reach for Eloquent models/migrations for app state; there's no persistence layer by design.

**Frontend app bootstrap** (`resources/js/main.js`) creates the Vue app and calls `registerPlugins(app)` (`resources/js/@core/utils/plugins.js`), which auto-registers every module under `resources/js/plugins/*.js` and `resources/js/plugins/*/index.js` as a Vue plugin (each exports a `default(app) => app.use(...)`). To wire up a new global plugin, just drop a file there — nothing else needs editing. Current plugins: `pinia`, `router`, `vuetify/*`, `iconify` (self-contained icon set, see below).

**Two component libraries coexist, both auto-registered** (no manual imports needed in templates) via `unplugin-vue-components` in `vite.config.js`, scanning `resources/js/@core/components` and `resources/js/components`:
- `resources/js/components/Base/*` — hand-rolled, Tailwind-styled primitives (Button, Form/*, Headless/* wrapping `@headlessui/vue`, Dropzone, Ckeditor, ToastNotification, VAlertDialog, ImageZoom, Lucide, LoadingIcon). Most use `tailwind-merge` + `lodash` for class merging — don't strip those deps without checking usage first.
- `resources/js/@core/*` and `resources/js/@layouts/*` — the Vuetify-based layout engine and dashboard widgets (`VerticalNavLayout`, `VerticalNav*`, card stat components). `@layouts` is the vertical-nav shell mechanics; `resources/js/layouts/*` (`default.vue`, `blank.vue`, `DefaultLayoutWithVerticalNav.vue`) is where that engine gets configured/skinned for this app (logo, nav items, footer).

Also globally auto-imported (no explicit `import` needed) via `unplugin-auto-import`: `vue`, `vue-router`, `@vueuse/core`, `pinia` APIs (`ref`, `computed`, `watch`, `h`, `useRouter`, etc.). Don't be surprised by files using these with no import statement — that's expected, not a bug.

**Styling is a Tailwind + Vuetify hybrid.** Tailwind (`tailwind.config.js`) drives utility classes and defines the brand palette as CSS custom properties (`--color-primary`, etc., set in the `addBase` plugin block, with a `.dark` override block — dark mode is `class`-based, toggled by `resources/js/store/dark-mode.js` on `<html>`). Vuetify's own theme (colors, component defaults) lives separately in `resources/js/plugins/vuetify/theme.js` / `defaults.js` / `icons.js` and must be kept in sync with the Tailwind palette by hand — there's no shared source of truth between the two systems. Vuetify SCSS lives under `resources/styles/@core` (aliased as `@core-scss`); layout SCSS under `resources/js/@layouts/styles` (aliased `@layouts`).

**Icons are prebuilt, not live-generated.** `resources/js/plugins/iconify/icons.css` is a committed, generated CSS file (mask-based, self-contained SVG data-URIs — no font files, no runtime dependency) providing all `bx-*` (Boxicons) icon classes used throughout the app, plus a handful of `mdi-*`/`fa-*` icons. It was built by `build-icons.js` from `@iconify-json/*` packages, which are **not** in `package.json` (only needed to regenerate the set — see `npm run build:icons` above). Vuetify's icon set (`resources/js/plugins/vuetify/icons.js`) renders any icon prop as a literal CSS class (its `iconify` icon set component just does `class: [props.icon]`), which is what makes `icons.css`'s classes work as Vuetify icons too — there's no `@mdi/font` or `@fortawesome/fontawesome-free` package installed; don't add icon usages that assume those exist.

**Path aliases** (defined once in `vite.config.js`, mirrored in `jsconfig.json`): `@` → `resources/js`, `@core` → `resources/js/@core`, `@layouts` → `resources/js/@layouts`, `@images` → `resources/images`, `@styles` → `resources/styles`, `@core-scss` → `resources/styles/@core`, `@configured-variables` → `resources/styles/variables/_template.scss`.

**No auth yet.** `resources/js/layouts/components/UserProfile.vue` shows a placeholder user; `resources/js/services/requests.js`'s `checkAuth()` is a no-op stub. When auth is built, that's the seam to wire real session-expiry handling back in.

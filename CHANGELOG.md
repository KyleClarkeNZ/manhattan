# Changelog

All notable changes to Manhattan will be documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Manhattan uses [Semantic Versioning](https://semver.org/).

---

## [1.2.2] — 2026-06-27

### Added
- `DataGrid::extraParams(array $params)` PHP builder — pass custom key/value pairs that are merged into every remote data request.
- `DataGrid` JS: `_extraParams` internal state (initialised from PHP config); `setExtraParams(params, merge?)` public method returns `this` for chaining then auto-refreshes the grid; `getExtraParams()` returns a copy of the current extra params.

---
## [1.2.9] — 2026-03-10

### Added
- `button.js`: `setLoading(bool)` public API method — `true` adds `m-button-loading` class and sets `disabled`; `false` reverses both. Prevents double-submission and provides visual feedback during AJAX calls.
- `window.js` `loadContent()`: shows a centred `.m-loader-spinner` inside `.m-window-content` immediately before the fetch starts; replaced by real content (or the styled error fragment) in all paths — success, non-2xx server error, and network failure.
- `tabs.js` `refreshContent()`: same loader-before-fetch behaviour; also adds error injection on non-2xx (was missing previously).

---

## [1.2.8] — 2026-03-10

### Added
- `m.dialog.confirm(message, title, iconClass)` — fully implemented. Returns a `Promise<boolean>` that resolves `true` on confirm, `false` on cancel / overlay click / Escape key. Uses the existing `.m-dialog-*` CSS classes with transition animation.
- `m.dialog.alert(message, title, iconClass)` — single-button alert dialog. Returns a `Promise<void>`.

### Fixed
- Both dialog methods were previously just a comment stub, causing a `TypeError` when called.

---

## [1.2.7] — 2026-03-10

### Fixed
- `window.js` `setContent()` and `loadContent()` were targeting `.m-window-body` which does not exist in the rendered HTML; corrected to `.m-window-content`.

---

## [1.2.6] — 2026-03-10

### Changed
- `window.js` `setContent()`: switched from `innerHTML` assignment to `createContextualFragment` + `appendChild` so that `<script>` tags embedded in lazily-loaded partial views are executed.
- `window.js` `loadContent()`: added `.catch()` handler — on a non-2xx response the server's HTML body (`error.data` from `m.ajax`, or raw text from the plain-fetch fallback) is injected into `.m-window-body` instead of failing silently. A styled `.partial-error` fallback is used when no body is available.
- Plain-`fetch` fallback in `loadContent()` now checks `r.ok` and injects error content on non-2xx, consistent with the `m.ajax` path.

---

## [1.2.5] — 2026-03-10

### Changed
- `tabs.js` `loadRemotePanel()`: on a non-2xx response, inject the server's HTML body (e.g. a styled error fragment) into the panel instead of a generic "Failed to load content" string. Both the `m.ajax` path (`error.data`) and the plain-`fetch` fallback now forward the response body.

---

## [1.2.4] — 2026-03-10

### Fixed
- `tabs.js`: replaced `innerHTML = html` with `createContextualFragment` + `appendChild` in `loadRemotePanel()`, `setContent()`, and `refreshContent()` so that `<script>` tags embedded in remotely-loaded tab content are executed correctly.

---

## [1.2.3] — 2026-03-10

### Added
- `Tabs` / `TabPanel`: `remoteUrl(string $url)` builder — lazy-fetches HTML into the panel on first activation via AJAX. The panel renders with a centred loader spinner until the request completes.
- `tabs.js`: `loadRemotePanel()` helper; `activateTab()` checks `data-remote-url` / `data-remote-loaded` and fires the new `m-tab-content-loaded` event after inject; the initially-active tab is also auto-loaded on `initSingleTabs()`.
- CSS: `.m-tabs-loader` centred flex container for the loading state.

---
## [1.2.1] — 2026-03-10

### Added
- **DataGrid `filterable()`** — adds a per-column live-search input row
  beneath the column headers.
  - Local data: client-side substring filtering across all active filter
    inputs, debounced at 250 ms. Resets to page 1 on each keystroke.
  - Remote data: active filters are appended to the request as
    `filterField[<field>]=<value>` query/body params for server-side handling.
  - Public API: `m.dataGrid('id').clearFilters()` clears all active filters
    and refreshes the grid.
  - Light and dark theme styles included.

---

## [1.1.0] — 2026-03-04

### Added
- Bundled **Font Awesome Free 6.x** (`components/font-awesome`) as a Composer
  dependency — no CDN required.
- `Installer::publishAssets()` now copies Font Awesome `css/` and `webfonts/`
  into `<public-dir>/Manhattan/fontawesome/` alongside Manhattan assets.
- `HtmlHelper::configure()` accepts an optional third argument
  `$fontAwesomeUrl` to override the public URL for the Font Awesome directory.
- `HtmlHelper::renderStyles()` now emits the Font Awesome stylesheet link
  automatically.

### Changed
- Demo no longer loads Font Awesome from a CDN; it is served from `vendor/`.

---

## [1.0.0] — 2026-03-04

### Added
- Initial extraction from the MyDay project as a standalone Composer package
- PSR-4 autoloading under the `Manhattan\` namespace
- PHP components: `Address`, `Badge`, `Breadcrumb`, `Button`, `Card`, `Chart`,
  `Checkbox`, `CodeArea`, `DataGrid`, `DatePicker`, `Dialog`, `Dropdown`,
  `EmptyState`, `HtmlHelper`, `Icon`, `Label`, `Loader`, `MList`, `NumberBox`,
  `PageHeader`, `ProgressBar`, `Radio`, `Rating`, `StatCard`, `TabPanel`,
  `Tabs`, `TextArea`, `TextBox`, `Toaster`, `ToggleSwitch`, `Validator`, `Window`
- CSS themes: `manhattan.css` (light) and `manhattan-dark.css` (dark)
- Vanilla-JS runtime: `manhattan.js`, `manhattan.ajax.js` and per-component files
  under `assets/js/components/`
- `Installer::publishAssets()` Composer script helper for consuming projects
- Standalone demo site in `demo/`

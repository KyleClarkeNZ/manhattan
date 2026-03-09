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

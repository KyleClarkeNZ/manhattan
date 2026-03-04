# Changelog

All notable changes to Manhattan will be documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Manhattan uses [Semantic Versioning](https://semver.org/).

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

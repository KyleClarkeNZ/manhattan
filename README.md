# Manhattan UI

Manhattan is a server-rendered PHP + vanilla-JS UI component library. Drop it into any PHP project and get a consistent, themeable set of UI components without needing a build pipeline.

**PHP 7.4+ · No build tools · jQuery ≥ 3.4.1 required · Font Awesome 6.x included**

---

## Requirements

| Dependency | Version | Notes |
|---|---|---|
| PHP | ≥ 7.4 | Server-side rendering |
| jQuery | ≥ 3.4.1 | Must be loaded by your application — not bundled |
| Font Awesome | 6.x | Bundled via Composer, served automatically |

> jQuery is the only dependency you need to bring yourself. Everything else is handled by Manhattan.

---

## Components

| Component | PHP class | JS module |
|---|---|---|
| Button | `Button` | `components/button.js` |
| TextBox | `TextBox` | `components/textbox.js` |
| TextArea | `TextArea` | `components/textarea.js` |
| Dropdown | `Dropdown` | `components/dropdown.js` |
| DatePicker | `DatePicker` | `components/datepicker.js` |
| Checkbox | `Checkbox` | — |
| Radio | `Radio` | — |
| ToggleSwitch | `ToggleSwitch` | `components/toggleswitch.js` |
| Tabs / TabPanel | `Tabs`, `TabPanel` | `components/tabs.js` |
| DataGrid | `DataGrid` | `components/datagrid.js` |
| Dialog | `Dialog` | `components/dialog.js` |
| Window | `Window` | `components/window.js` |
| Toaster | `Toaster` | `components/toaster.js` |
| Rating | `Rating` | `components/rating.js` |
| ProgressBar | `ProgressBar` | — |
| Badge | `Badge` | — |
| Card | `Card` | — |
| StatCard | `StatCard` | — |
| PageHeader | `PageHeader` | — |
| Breadcrumb | `Breadcrumb` | — |
| Address | `Address` | `components/address.js` |
| MList | `MList` | `components/list.js` |
| Chart | `Chart` | `components/chart.js` |
| CodeArea | `CodeArea` | `components/codearea.js` |
| Validator | `Validator` | `components/validator.js` |
| Loader | `Loader` | — |
| EmptyState | `EmptyState` | — |
| Icon | `Icon` | — |
| Label | `Label` | — |
| NumberBox | `NumberBox` | — |

---

## Installation

Manhattan is a public Composer package hosted on GitHub. No authentication is required.

### 1 — Add Manhattan to your `composer.json`

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/KyleClarkeNZ/manhattan"
        }
    ],
    "require": {
        "kyleclarkenz/manhattan": "^1.0"
    },
    "extra": {
        "manhattan": {
            "public-dir": "."
        }
    },
    "scripts": {
        "post-install-cmd": ["Manhattan\\Installer::publishAssets"],
        "post-update-cmd":  ["Manhattan\\Installer::publishAssets"]
    }
}
```

> **`public-dir`** is the folder relative to your project root where web-accessible files live. Use `"."` for legacy PHP projects where `index.php` sits at the root, or `"public"` for Laravel/Symfony-style layouts. After install, assets (including Font Awesome) will be published to `<public-dir>/Manhattan/`.

### 2 — Install

```bash
composer install
```

Manhattan's CSS, JS, and Font Awesome assets are copied to `<public-dir>/Manhattan/` automatically.

### 3 — Configure asset paths in your bootstrap

Call this once before any views are rendered, adjusting the paths to match your `public-dir`:

```php
use Manhattan\HtmlHelper;

HtmlHelper::configure(
    '/Manhattan/assets/css',   // web-root-relative path to Manhattan CSS
    '/Manhattan/assets/js'     // web-root-relative path to Manhattan JS
);
```

If you're serving Font Awesome from a non-standard location, pass the path as a third argument:

```php
HtmlHelper::configure(
    '/Manhattan/assets/css',
    '/Manhattan/assets/js',
    '/custom/fontawesome'      // optional: override Font Awesome public path
);
```

### 4 — Include styles & scripts in your layout

```php
// In <head> — renders Manhattan CSS + Font Awesome automatically:
<?= $m->renderStyles() ?>
<?php if ($isDarkTheme): echo $m->renderDarkStyles(); endif; ?>

// Before </body> (or deferred in <head>):
<?= $m->renderScripts() ?>
```

---

## Updating

```bash
composer update kyleclarkenz/manhattan
```

Assets are re-published automatically on every update.

---

## Running the demo locally

```bash
cd /path/to/manhattan
composer install
php -S localhost:8080
```

Then open **http://localhost:8080/demo/** in your browser.

---

## Usage

Manhattan uses a simple singleton pattern — grab the `HtmlHelper` instance and start rendering components using the fluent API:

```php
use Manhattan\HtmlHelper;

$m = HtmlHelper::getInstance();

// Render a button
echo $m->button('saveBtn', 'Save')->primary()->icon('fa-save');

// Render a dropdown
echo $m->dropdown('priority')
    ->dataSource([
        ['value' => '1', 'text' => 'Low'],
        ['value' => '2', 'text' => 'High'],
    ])
    ->placeholder('Select priority…');

// Render a date picker
echo $m->datepicker('dueDate')->value(date('Y-m-d'))->min(date('Y-m-d'));
```

---

## Theming

Manhattan ships with a light theme (`manhattan.css`) and a dark theme (`manhattan-dark.css`). Both are included automatically via `renderStyles()` — toggle the `m-dark` class on `<body>` (or `<html>`) to switch themes at runtime.

---

## Icons

Font Awesome Free 6.x is bundled as a Composer dependency and published alongside Manhattan's assets. No CDN or separate download needed — just use Font Awesome class names in your components as normal:

```php
echo $m->button('deleteBtn', 'Delete')->danger()->icon('fa-trash');
```

Font Awesome is licensed under the [MIT License](https://opensource.org/licenses/MIT) (icons) and [SIL OFL 1.1](https://scripts.sil.org/OFL) (fonts).

---

## License

Manhattan is open source and released under the [MIT License](LICENSE).

© Kyle Clarke

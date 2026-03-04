# Manhattan UI

A server-rendered PHP + vanilla-JS UI component library.  
PHP 7.4+, no build tools, zero runtime dependencies.

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

Manhattan is distributed as a **private Composer package** hosted on GitHub.

### 1 — Authenticate with GitHub

Generate a [GitHub personal access token](https://github.com/settings/tokens) with
`read:packages` (and `repo` scope for private repositories), then add it to your
Composer auth config **once** on each machine / CI server:

```bash
composer config --global github-oauth.github.com <YOUR_TOKEN>
```

### 2 — Add the VCS repository to your project's `composer.json`

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

> **`public-dir`** is the folder relative to your project root where web-accessible
> files live.  Use `"."` for legacy PHP projects where `index.php` is at the root.
> Use `"public"` for Laravel/Symfony-style layouts.  After installation, assets will
> be available at `<public-dir>/Manhattan/assets/css/` and
> `<public-dir>/Manhattan/assets/js/`.

### 3 — Install

```bash
composer install
```

Assets are copied to `<public-dir>/Manhattan/` automatically.

### 4 — Configure asset URLs in your bootstrap

```php
use Manhattan\HtmlHelper;

// Adjust paths to match where composer published the assets
HtmlHelper::configure(
    '/Manhattan/assets/css',  // web-root-relative CSS path
    '/Manhattan/assets/js'    // web-root-relative JS path
);
```

### 5 — Include styles & scripts in your layout

```php
// In <head>:
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

Manhattan ships with a light theme (`manhattan.css`) and a dark theme
(`manhattan-dark.css`).  Include both files and toggle the `m-dark` class on
`<body>` (or `<html>`) to switch at runtime.

---

## License

Proprietary — © Kyle Clarke.  All rights reserved.

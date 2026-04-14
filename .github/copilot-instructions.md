# GitHub Copilot Instructions for Manhattan UI

## Project Overview
Manhattan is a **server-rendered PHP + vanilla-JS** UI component library (`kyleclarkenz/manhattan`). It has no build-step and zero runtime dependencies. PHP 7.4+ is fully supported.

## Core Architecture

### Component Pattern
- **PHP Component Classes** (src/) - Server-side builders that generate HTML with data attributes
- **JavaScript Modules** (assets/js/components/) - Auto-initialize components based on data attributes and CSS classes
- **CSS Styling** (assets/css/manhattan.css, manhattan-dark.css) - Complete styling without CSS frameworks

Components follow this workflow:
```php
// PHP: Fluent builder pattern
echo $m->button('btnId', 'Click Me')
    ->primary()
    ->icon('fa-save')
    ->disabled();

// Renders to HTML with data attributes
<button id="btnId" class="m-button m-button-primary" disabled>
    <i class="fas fa-save"></i> Click Me
</button>

// JS auto-initializes on DOMContentLoaded
m.button('btnId') // returns API object
```

### HtmlHelper Singleton
`$m = HtmlHelper::getInstance()` is the entry point for all components. It provides factory methods:
- `$m->button()`, `$m->textbox()`, `$m->window()`, etc.
- `$m->icon()` - Font Awesome icon helper
- `$m->renderStyles()`, `$m->renderScripts()` - Asset inclusion
- `$m->ajax()` - AJAX helper with CSRF token support

## PHP 7.4 Constraints

**CRITICAL**: Target PHP 7.4. Do NOT use PHP 8.0+ features:
- ❌ Constructor property promotion
- ❌ Named arguments
- ❌ Match expressions
- ❌ Nullsafe operator (`?->`)
- ❌ Union types beyond nullable
- ❌ `str_contains()`, `str_starts_with()`, `str_ends_with()`
- ✅ Nullable types ARE supported (`?string`, `?int`)
- ✅ Typed properties ARE supported (`private string $name;`)
- ✅ Arrow functions ARE supported (`fn($x) => $x * 2`)

Use strict types: `declare(strict_types=1);`

## Component Development Standards

### Universal Option Pattern
**CRITICAL**: All component options follow this pattern:
- **Default value**: `false` if not present
- **Enabling**: Call method without parameters or with `true`
- **Disabling**: Call method with `false`

Examples:
```php
// Default is false (non-modal, non-draggable, etc.)
$m->window('win', 'Title')  // non-modal window

// Enable by calling method (no parameter = true)
->modal()        // same as ->modal(true)
->draggable()    // same as ->draggable(true)

// Explicitly disable
->modal(false)
->draggable(false)
```

This applies to:
- `->modal()` - Window/overlay behavior
- `->draggable()` - Drag functionality
- `->primary()`, `->secondary()`, `->danger()` - Button styles
- `->checked()` - Checkbox/toggle state
- `->disabled()` - Disabled state
- All boolean fluent methods

### Component Behavior Patterns

#### Windows
- **Non-modal (default)**: No overlay, draggable by default, z-index stacking on click
- **Modal (`->modal()`)**: Overlay, blocks interaction, NOT draggable (unless `->draggable()` is explicitly called)
- Non-draggable windows should NOT show move cursor on title bar hover

#### Fluent API
All components support method chaining:
```php
echo $m->button('save', 'Save')
    ->primary()
    ->icon('fa-save')
    ->disabled()
    ->addClass('custom-class')
    ->attr('data-foo', 'bar');
```

#### PHP Component Base Class
All components extend `Manhattan\Component`:
```php
abstract class Component
{
    protected string $id;
    protected array $classes = [];
    protected array $attributes = [];
    
    abstract protected function getComponentType(): string;
    abstract protected function renderHtml(): string;
    
    public function addClass(string $class): self
    public function attr(string $name, string $value): self
    public function render(): string
}
```

### JavaScript Component Pattern
Components auto-initialize via `manhattan.js`:
```javascript
(function(window) {
    'use strict';
    const m = window.m;
    const utils = m.utils;

    m.componentName = function(id, options) {
        const el = utils.getElement(id);
        if (!el) return null;
        
        // Read config from data attributes set by PHP
        const someConfig = el.getAttribute('data-config') === 'true';
        
        // Component logic...
        
        return {
            // Public API
            methodName: function() { /* ... */ }
        };
    };
})(window);
```

## Demo Page Standards

### CRITICAL: Demo Page Maintenance
**When adding or modifying component features, ALWAYS update the demo page immediately.** This includes:

1. **Live Examples** - Add interactive demos showcasing the new feature
2. **Code Tabs** - Update PHP and JS code examples with new methods/options
3. **API Tables** - Document new methods in the PHP/JS API tables
4. **Events Table** - Document any new events
5. **Default Behavior** - Clearly state defaults in descriptions

Demo pages live in `/demo/pages/[component].php` and follow this pattern:
```php
<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-icon') ?> Component Name</h2>
    <p class="m-demo-desc">Brief description, **including default behavior**.</p>

    <h3>Example Name</h3>
    <p class="m-demo-desc">Explain what this example demonstrates.</p>
    <div class="m-demo-row">
        <!-- Interactive demo controls -->
    </div>
    
    <!-- Component instance -->
    <?= $m->component('demo-id')->option() ?>
    
    <div class="m-demo-output" id="output-id">Output appears here...</div>
    
    <!-- Code tabs with PHP and JS examples -->
    <?= demoCodeTabs($phpCode, $jsCode) ?>
</div>

<!-- API Documentation -->
<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->component($id)', 'string', 'Create component instance.'],
    ['->option($val)', 'type', 'Description. Default: <code>value</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.component(id)', 'string', 'Get component instance.'],
    ['method()', '', 'Description.'],
]) ?>

<?= eventsTable([
    ['m:component:event', '{detail}', 'Fired when...'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Demo interaction logic
});
</script>
```

### API Table Helpers
Use these helpers (defined in `/demo/_helpers.php`):
```php
demoCodeTabs($phpCode, $jsCode)  // Creates tabbed code viewer
apiTable($title, $badge, $rows)  // PHP/JS API reference
eventsTable($rows)                // Events documentation
```

## Versioning & Publishing

### Version Management
The `auto-tag.yml` GitHub Actions workflow **automatically creates and pushes a version tag** whenever you push to `master`. The tag is applied directly to your pushed commit — CI does **not** commit anything back to `master`.

**IMPORTANT — do NOT manually create tags.** The CI handles tagging automatically:
- `feat:` or `feature:` prefix → minor bump (e.g. `v1.5.0`)
- `fix:`, `chore:`, `docs:`, etc. → patch bump (e.g. `v1.5.1`)
- `BREAKING:` or `major:` prefix → major bump (e.g. `v2.0.0`)

**Version workflow:**
1. Bump `composer.json` version field manually in your commit (e.g. `"version": "1.5.0"`) to keep it in sync — this is informational only
2. Push to `master` — the CI tags automatically, no further action needed
3. Downstream projects on path repositories see the new commit immediately; those on tagged releases run `composer update kyleclarkenz/manhattan`

**Do NOT** run `git tag` manually — if you create a tag that matches what CI calculates, CI will skip tagging (it checks first), which is fine. But creating tags locally and pushing them can cause the CI to skip versions unintentionally.

### Asset Publishing
`composer install` / `composer update` automatically runs `Manhattan\Installer::publishAssets`, which copies:
- `/assets/css/` → `/Manhattan/css/`
- `/assets/js/` → `/Manhattan/js/`
- Font Awesome → `/Manhattan/fontawesome/` (from `vendor/components/font-awesome`)

**NEVER** edit published files in `/Manhattan/` directly — changes will be overwritten.

## Font Awesome Integration
- Font Awesome 6 served from `/Manhattan/fontawesome/` (published by Composer)
- **NEVER** add manual `<link>` tags — `$m->renderStyles()` includes Font Awesome
- Use `<?= $m->icon('fa-save') ?>` in views
- Raw HTML: `<i class="fas fa-icon-name"></i>`

## Testing
- Use PHP built-in server: `php -S localhost:8081` from manhattan/ root
- Demo available at: `http://localhost:8081/demo/index`
- Individual component pages: `http://localhost:8081/demo/[component]`
- Check for PHP errors: `grep -i "error\|warning" <(curl -s url)`

## Build & Distribution
- `./build.sh` creates `dist/manhattan-demo.zip` with production files
- Includes: src/, assets/, demo/, vendor/autoload.php, composer.json
- Generates .htaccess with rewrite rules for demo sub-pages

## Common Tasks

### Adding a New Component
1. Create PHP class in `src/ComponentName.php` extending `Component`
2. Create JS module in `assets/js/components/componentname.js`
3. Add CSS to `assets/css/manhattan.css` and `manhattan-dark.css`
4. Create demo page in `demo/pages/componentname.php`
5. Add route to `$demoNav` array in `demo/index.php`
6. Test thoroughly, update API docs

### Modifying Existing Component
1. Make changes to PHP/JS/CSS as needed
2. **IMMEDIATELY** update demo page:
   - Add new examples if behavior changed
   - Update code tabs with new usage
   - Update API tables with new methods/options/defaults
   - Add to events table if new events
3. Test changes in demo
4. Update version in composer.json
5. Commit, tag, publish

### Fixing Bugs
1. Reproduce issue in demo page
2. Fix in source (src/, assets/)
3. Verify fix in demo
4. Ensure demo documents correct behavior
5. Commit with clear message

## Code Style

### PHP
- PSR-1, PSR-2/PSR-12 standards
- Strict types: `declare(strict_types=1);`
- Type hints on all methods: `public function foo(string $bar): self`
- Fluent methods return `$this` / `self`
- Private/protected properties: `protected string $name;`
- Constants: `UPPER_SNAKE_CASE`

### JavaScript
- IIFE pattern: `(function(window) { 'use strict'; ... })(window);`
- Vanilla JS - no frameworks/libraries
- ES5 syntax for broad compatibility (arrow functions OK but prefer `function`)
- Use `utils.getElement()` for DOM queries
- Trigger custom events: `utils.trigger(element, 'event-name', detail)`

### CSS
- BEM-inspired: `.m-component`, `.m-component-element`, `.m-component--modifier`
- Dark theme: `body.m-dark .m-component { ... }`
- Mobile-first media queries: `@media (min-width: 768px)`
- CSS variables for theming: `var(--m-primary, #118AB2)`

## Philosophy
- **Zero dependencies** - no frameworks, no build tools
- **Server-first** - PHP generates semantic HTML
- **Progressive enhancement** - works without JS, better with it
- **Accessible** - ARIA attributes, keyboard navigation
- **Flexible** - works in any PHP 7.4+ environment
- **Documented** - every feature demonstrated and explained in demo pages

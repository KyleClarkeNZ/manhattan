# Build System Documentation

## Overview

The Manhattan build system automates PHP 7.4 compatibility testing and creates production-ready deployment packages for the component demo site.

## Quick Start

### Build for Production

```bash
./build.sh
```

This will:
1. ✅ Run PHP 7.4 compatibility checks
2. 📦 Create `dist/manhattan-demo.zip` with demo files
3. 🔢 Auto-increment version number
4. 📊 Display build summary

### Output

- **Build artifact**: `dist/manhattan-demo.zip`
- **Target URL**: https://manhattan.kyleclarke.co.nz
- **Size**: ~896KB compressed (~2.5MB uncompressed)
- **Files**: ~104 files (production-ready)
- **Contents**: Production-ready demo application

## What Gets Included

The build package includes **only production-required files**:
- ✅ `index.php` - Root entry point
- ✅ Demo files (`demo/index.php`, `demo/_view.php`)
- ✅ Manhattan source (`src/`) - Required by Composer autoloader
- ✅ Published assets (`assets/css`, `assets/js`)
- ✅ Vendor dependencies (Font Awesome, autoloader)
- ✅ `.htaccess` - Production web server config

## What Gets Excluded

The following are **not** included in production builds:
- ❌ `bin/` - Development scripts
- ❌ `scripts/` - Development/build tools
- ❌ `.git/` - Version control
- ❌ `composer.json` / `composer.lock` - Not needed (vendor/ is pre-built)
- ❌ `CHANGELOG.md`, `README.md`, `BUILD.md` - Documentation
- ❌ `build.sh`, `VERSION` - Build tools
- ❌ IDE files (`.vscode/`, `.idea/`)
- ❌ Log files (`*.log`)
- ❌ Backup files (`*.bak`, `*.backup`)
- ❌ System files (`.DS_Store`, `Thumbs.db`)
- ❌ Test directories

## Pre-Deployment Checks

The build script runs compatibility tests before packaging:

### 1. PHP 7.4 Compatibility Check (Optional)
If `scripts/check_php74_compatibility.php` exists, scans all PHP files for PHP 8.0+ syntax:
- Constructor property promotion
- Nullsafe operator (`?->`)
- Match expressions
- `str_contains()`, `str_starts_with()`, `str_ends_with()`
- Union types (beyond nullable)
- Mixed type hints

### 2. Version Bumping
Automatically increments the patch version in `VERSION` file:
- Format: `major.minor.patch` (e.g., 1.3.02)
- Patch is zero-padded to 2 digits
- Auto-increments patch on each build
- Rolls over to next minor/major when reaching 99

## Deployment Workflow

### 1. Build the Package

```bash
./build.sh
```

### 2. Upload to Server

Upload `dist/manhattan-demo.zip` to your web server at `manhattan.kyleclarke.co.nz`.

### 3. Extract

```bash
cd /path/to/manhattan.kyleclarke.co.nz
unzip manhattan-demo.zip
```

### 4. Set Permissions

```bash
chmod -R 755 .
```

### 5. Verify .htaccess

The build script creates a `.htaccess` file automatically if it doesn't exist. This file:
- Serves `index.php` as the directory index (root entry point)
- Serves assets directly
- Handles demo endpoints (`/toggleTheme`, `/nzpostSuggest`)
- Sets security headers
- Disables directory browsing

The root `index.php` file includes the demo and ensures all paths work correctly whether accessed from `/` or `/demo/`.

### 6. Test

Visit https://manhattan.kyleclarke.co.nz to verify the demo is working correctly.

## Directory Structure (Production)

After deployment, the production directory should look like:

```
manhattan.kyleclarke.co.nz/
├── index.php           # Root entry point
├── .htaccess           # URL rewriting and security
├── demo/
│   ├── index.php       # Demo bootstrap
│   └── _view.php       # Demo content/components
├── src/                # Manhattan component classes (required by autoloader)
│   ├── HtmlHelper.php
│   ├── Button.php
│   ├── DataGrid.php
│   └── [all other components]
├── assets/
│   ├── css/
│   │   ├── manhattan.css
│   │   └── manhattan-dark.css
│   └── js/
│       ├── manhattan.js
│       ├── manhattan.ajax.js
│       └── components/
└── vendor/
    ├── autoload.php
    └── components/
        └── font-awesome/
```

## Environment Variables (Optional)

The demo supports an optional NZ Post Address autocomplete API key:

```bash
export NZPOST_SUBSCRIPTION_KEY=your_key_here
```

This enables the Address component demo. If not set, the address component will show a friendly error message.

## Troubleshooting

### Build Fails with Composer Error

Ensure Composer is installed:
```bash
composer --version
```

If not installed, download from https://getcomposer.org

### Assets Not Loading

1. Verify .htaccess is in place
2. Check that `mod_rewrite` is enabled in Apache
3. Ensure vendor/components/font-awesome directory exists

### Demo Shows Blank Page

1. Check PHP error logs
2. Verify PHP version is 7.4 or higher
3. Ensure autoloader is working: `vendor/autoload.php` exists

### Theme Toggle Not Working

The demo uses PHP sessions for theme persistence. Ensure:
1. Session directory is writable
2. PHP session functions are enabled
3. Browser cookies are enabled

## Manual Testing

You can test the demo locally before building:

```bash
cd /home/kyle/manhattan
php -S localhost:8080
```

Then visit: http://localhost:8080/demo/

## Version History

The `VERSION` file tracks the build version. Format: `major.minor.patch`

- **Major**: Breaking changes to component APIs
- **Minor**: New components or significant features
- **Patch**: Bug fixes, documentation, demo updates

Example: `1.3.02` = version 1.3, patch level 2

## Related Files

- `build.sh` - Build automation script
- `VERSION` - Current build version
- `composer.json` - Dependency manifest
- `.htaccess` - Production web server config
- `demo/index.php` - Demo bootstrap
- `demo/_view.php` - Component showcase

## Support

For issues or questions:
- GitHub: https://github.com/KyleClarkeNZ/manhattan
- Demo: https://manhattan.kyleclarke.co.nz

# Manhattan Versioning Guide

## How It Works

Manhattan now uses **automated git tag-based versioning** instead of a VERSION file.

### Automatic Versioning (GitHub Actions)

When you push commits to the `master` branch, a GitHub Actions workflow automatically:
1. Detects the commit type based on the commit message
2. Calculates the next version number
3. Updates `composer.json` with the new version
4. Creates and pushes a git tag

### Commit Message Format

Use **conventional commits** to control version bumping:

| Commit Prefix | Version Bump | Example |
|---------------|--------------|---------|
| `fix:` | Patch (1.3.10 → 1.3.11) | `fix: correct radio button alignment` |
| `feat:` or `feature:` | Minor (1.3.10 → 1.4.0) | `feat: add new carousel component` |
| `BREAKING:` or `major:` | Major (1.3.10 → 2.0.0) | `BREAKING: change API structure` |
| `chore:`, `docs:`, `refactor:` | Patch (default) | `chore: update dependencies` |
| `[skip ci]` anywhere | No version bump | `docs: fix typo [skip ci]` |

### Manual Versioning

If you prefer manual control or GitHub Actions didn't run:

```bash
# 1. Update composer.json version
vim composer.json  # Change "version": "1.3.10" to "1.3.11"

# 2. Commit the version change
git add composer.json
git commit -m "chore: bump version to 1.3.11 [skip ci]"

# 3. Create annotated tag
git tag -a v1.3.11 -m "Release v1.3.11

- Fix: radio button centering
- Improve: demo examples"

# 4. Push everything
git push origin master
git push origin v1.3.11
```

## Build Script

The build script (`./build.sh`) now only:
1. Runs PHP 7.4 compatibility checks
2. Installs composer dependencies
3. Creates deployment package (dist/manhattan-demo.zip)

**It does NOT bump versions** — that's handled by git tags.

## Version Synchronization

The version in `composer.json` should always match the latest git tag:
- Git tag: `v1.3.11`
- Composer.json: `"version": "1.3.11"`

The GitHub Actions workflow keeps these in sync automatically.

## Checking Current Version

```bash
# Latest git tag
git describe --tags --abbrev=0

# Composer.json version
grep '"version"' composer.json
```

## Troubleshooting

### GitHub Actions didn't create a tag

Check the Actions tab on GitHub for errors. Common issues:
- Workflow file syntax errors
- Insufficient permissions (needs `contents: write`)
- Tag already exists

### Version out of sync

If `composer.json` and git tags get out of sync, manually fix:

```bash
# Check latest tag
LATEST_TAG=$(git describe --tags --abbrev=0)

# Update composer.json to match
# Edit "version" field to match (without 'v' prefix)

git add composer.json
git commit -m "chore: sync version with tags [skip ci]"
git push origin master
```

## For Downstream Projects (CallSheet, etc.)

To get the latest Manhattan version:

```bash
composer update kyleclarkenz/manhattan
```

This pulls the version specified in the git tag, not from a VERSION file.

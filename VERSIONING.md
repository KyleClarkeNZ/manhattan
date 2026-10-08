# Manhattan Versioning Guide

## How It Works

Manhattan uses **automated git tag-based versioning**. There is no VERSION file,
and — deliberately — **no `version` field in `composer.json`**.

> **Do not reintroduce `"version"` into `composer.json`.**
> Composer's VCS driver prefers that field over the git tag it found, so every
> tag reports whatever the field says. A stale field silently republished tags
> v1.34.0 – v1.52.2 as "1.33.0", and downstream projects could not resolve any
> version above it — they had to pin `dev-master` instead of using `^1.x`.
> Composer's own guidance is to omit `version` for VCS-hosted packages and let
> the tags speak for themselves.

### Automatic Versioning (GitHub Actions)

When you push commits to the `master` branch, a GitHub Actions workflow automatically:
1. Detects the commit type based on the commit message
2. Calculates the next version number
3. Creates and pushes a git tag

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
# 1. Create annotated tag (nothing to edit — composer.json carries no version)
git tag -a v1.3.11 -m "Release v1.3.11

- Fix: radio button centering
- Improve: demo examples"

# 2. Push everything
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

Nothing to synchronise — the git tag is the single source of truth.

## Checking Current Version

```bash
# Latest git tag — this IS the version
git describe --tags --abbrev=0
```

## Troubleshooting

### GitHub Actions didn't create a tag

Check the Actions tab on GitHub for errors. Common issues:
- Workflow file syntax errors
- Insufficient permissions (needs `contents: write`)
- Tag already exists

### A downstream project cannot see recent versions

Check that `composer.json` still has no `version` field. If one has been added
back, remove it and re-tag — until then Composer reports every tag as that
field's value.

```bash
grep '"version"' composer.json   # should print nothing
```

## Local Path Repositories

A project that symlinks a local checkout (a Composer `path` repository) sees it
as `dev-master`, not a tagged version, because the path driver reads the branch.
`composer.json` aliases `dev-master` to `1.x-dev`, so such a project can require:

```json
"kyleclarkenz/manhattan": "^1.54@dev"
```

Keep the alias's major version in step with the tags when cutting a 2.0.

## For Downstream Projects (CallSheet, etc.)

To get the latest Manhattan version:

```bash
composer update kyleclarkenz/manhattan
```

This pulls the version specified in the git tag, not from a VERSION file.

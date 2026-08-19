# Changelog

All notable changes to `filament-header-schema` will be documented in this file.

## v1.0.3 - Dependency Constraint Fixes - 2026-08-19

> **If you are on v1.0.2, upgrade.** That release declared `filament/filament: ^5.0`, but the components construct `Filament\Support\View\ComponentAttributeBag`, which does not exist before Filament v5.7.0. Installing v1.0.2 against Filament 5.0–5.6 resolves successfully and then fatals at render time with a missing class. The requirement is back to `^5.7`, which is the version this package has always actually needed.

### Fixed

- Restored the `filament/filament: ^5.7` requirement, reverting the incorrect widening to `^5.0` shipped in v1.0.2.
- Raised `orchestra/testbench` to `^9.7`. `testbench.yaml` points `laravel` at the `@testbench` skeleton alias, which testbench-core only learned to resolve in v9.7.0 — older versions treat it as a literal path and abort `package:discover` during install.
- Widened the Pest requirement to span both major lines (`^3.8||^4.1`). Pest v4 requires PHP ^8.3, so the PHP 8.2 legs could never install; but `pest-plugin-laravel`'s v3 line never gained Laravel 13 support, so neither major covers the full support matrix alone.
- Corrected the CI matrix to pin testbench with constraints rather than wildcards. The install step overwrites whatever `composer.json` declares, so `9.*` let `--prefer-lowest` select versions below the supported floor regardless of the manifest.

### Security

- Added `SECURITY.md`, so vulnerability reports have a documented private channel instead of a public issue.
- Pinned all 12 third-party GitHub Action references to full commit SHAs. A tag can be silently re-pointed by a compromised maintainer — the mechanism behind the March 2025 `tj-actions/changed-files` incident.
- Raised Dependabot's cooldown from 1 to 3 days, so a malicious release has a window to be identified before it is pulled in.

<!-- Release notes generated using configuration in .github/release.yml at main -->
### What's Changed

#### Bug fixes

* Fix PHP 8.2 CI matrix legs by @acepoblete in https://github.com/VitisStudio/filament-header-schema/pull/2

#### Other changes

* Security hardening by @acepoblete in https://github.com/VitisStudio/filament-header-schema/pull/1

### New Contributors

* @acepoblete made their first contribution in https://github.com/VitisStudio/filament-header-schema/pull/2

**Full Changelog**: https://github.com/VitisStudio/filament-header-schema/compare/v1.0.2...v1.0.3

## v1.0.2 - Widen Filament Support - 2026-08-18

### Fixed

- Lowered the Filament requirement from `^5.7` to `^5.0`. The 5.7 floor was never a real requirement — every API this package uses has existed unchanged since Filament 5.0.0, so pinning to 5.7 excluded five minor releases of installs for no reason.

## v1.0.1 - Plugin Directory Cleanup - 2026-08-18

Cleanup pass on the assets and CI config needed for the Filament plugin directory submission — no code changes.

### Fixed

- Replaced `art/hero.png` with a 2560×1440 JPEG. The plugin directory rejects submission artwork below that resolution; the previous hero was 1672×941.
- Fixed two `.github` workflow files still carrying unreplaced package-skeleton placeholders (`:vendor_slug/:package_name`). The broken one mattered: the Dependabot auto-merge guard compared `github.repository` against a literal placeholder, so the condition could never be true and no Dependabot PR has ever auto-merged.

### Added

- `.github/release.yml`, so GitHub groups future generated release notes by pull request label instead of listing every merged PR flat.

## v1.0.0 - Initial Release - 2026-08-18

Build the header of a Filament page with a schema instead of a Blade view.

Filament gives you `getHeading()` and `getSubheading()` for plain text, and `getHeader()` for everything else — which means dropping to a Blade view the moment a header needs an avatar, a status badge and a couple of totals. This package adds a `headerSchema()` method that sits alongside `form()` and `infolist()`, so a rich header is written the same way as the rest of the page.

### What's in it

**`HasHeaderSchema`** — a trait for any resource page. It resolves `{Model}Header` from the resource's `Schemas` directory by convention, the same way `OrderForm` and `OrderInfolist` are found, or takes a `headerSchema()` method declared on the page. A page with neither falls back to Filament's native heading, so the trait is safe to apply to a base class and opt pages in over time.

**`make:filament-header-schema`** — generates the schema class and applies the trait to the resource pages you select, editing them in place and leaving the rest of the file alone.

**Three components** — `Heading` (a real `<h1>`–`<h6>`, sized and weighted to match Filament's own), `Subheading` (the muted supporting line), and `HeaderSection` (the flexbox layout a rich header wants, with leading, main and trailing slots). Every slot takes what a schema takes, including actions.

**No build step** — the styles are plain CSS written against Filament's design tokens and inlined into the page head. Dark mode and custom panel palettes work without publishing an asset or touching a theme.

### Requirements

- PHP 8.2+
- Laravel 11.28, 12 or 13
- Filament 5.7+

```bash
composer require vitisstudio/filament-header-schema




```
See the [README](https://github.com/VitisStudio/filament-header-schema#readme) for the full documentation, and `workbench/` for a demo panel covering every path through the package.

**Full Changelog**: https://github.com/VitisStudio/filament-header-schema/commits/v1.0.0

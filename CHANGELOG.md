# Changelog

All notable changes to `filament-header-schema` will be documented in this file.

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

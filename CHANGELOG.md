# Changelog

All notable changes to this project are documented here. Format follows
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/). Until the API
stabilizes at 1.0 a `0.0.x` bump may carry breaking changes.

## [Unreleased]

## [0.0.19] - 2026-09-22

### Changed

- Accepts `telegram-bot-essentials/essence` `^0.14` as well as `^0.13`:
  0.14.0 only removed `DoneLimited`/`CannotSetItAsDone`/`HidesDone`, none of
  which this package uses.

## [0.0.18] - 2026-09-20

### Fixed

- Declares `brick/math` `>=0.14.2`: the rounding code uses the `HalfUp` / `Down`
  enum cases introduced there, but the package only inherited brick/math through
  laravel/framework, so a resolution pinned to an older release failed with an
  undefined constant.

## [0.0.17] - 2026-09-20

### Changed

- **BREAKING:** requires `telegram-bot-essentials/essence` `^0.13` (the JSON user
  state and the forms engine). No code change: the package's suite passes
  against essence 0.13.0.

## [0.0.16] - 2026-09-01

### Changed

- **BREAKING:** requires `telegram-bot-essentials/essence` `^0.12`. Handlers
  are locale-lazy.

### Added

- `/affiliation` command (an essence `Command` subclass registered via
  `commandBus()->addCommands()`) that opens the same menu as the reply-key
  entry (0.0.15).
- Pest test suite, Laravel Pint, Larastan (level max), GitHub Actions CI,
  Laravel Workbench, `LICENSE` (MIT) and this changelog.

### Removed

- The `phpstan-bootstrap.php` `ExceptionHandler` recursion stub — essence's
  handler now guards its own fallback path.

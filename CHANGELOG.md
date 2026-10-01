# Changelog

All notable changes to this project are documented here. Format follows
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/). Until the API
stabilizes at 1.0 a `0.0.x` bump may carry breaking changes.

## [Unreleased]

### Removed

- No longer appends `AffiliationCommand` to `tbe-essence.commands`. essence
  builds the Telegram menu from the command bus, where the command is still
  registered.

### Changed

- Requires essence `^0.16.2`, the first release that builds the menu from
  the command bus; on older essence `/affiliation` would drop out of it.

## [0.0.26] - 2026-10-01

### Fixed

- The Persian admin strings call the program زیرمجموعه‌گیری, like the rest of the package.

## [0.0.25] - 2026-10-01

### Added

- An admin "Affiliates" menu: every affiliate with their referral count and earnings, the program's totals on top, sortable by either, with a page jump. A row opens the member's affiliation screen, which goes back to the list.
- With user-management installed, the user list can be sorted by referrals and by affiliate earnings.

## [0.0.24] - 2026-10-01

### Fixed

- Static analysis passes with the optional user-management section classes present.

## [0.0.23] - 2026-10-01

### Added

- An admin view of a member's affiliation (who referred them, their code, referrals and earnings), reached from an "Affiliation" button on the member's profile when user-management is installed.

## [0.0.22] - 2026-10-01

### Changed

- Reworded the user-facing English and Persian strings to read more naturally; no keys or placeholders changed.

## [0.0.21] - 2026-09-28

### Changed

- Accepts essence 0.16 alongside 0.15.

## [0.0.20] - 2026-09-27

### Changed

- Requires essence `^0.15`, which fills the `{placeholders}` in these
  messages.
- Affiliate log messages name the amount, invoice and referral they are
  about (`Commission 5000 credited for invoice #12 (referral #3)`).

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

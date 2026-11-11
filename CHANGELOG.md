# Changelog

All notable changes to this project are documented here. Format follows
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/). Until the API
stabilizes at 1.0 a `0.0.x` bump may carry breaking changes.

## [Unreleased]

## [0.0.36] - 2026-11-11

### Added

- Give affiliates their bot and typed relations.
- List affiliates and show one's referrals and credits.
- Add the affiliates panel plugin.
- Add the affiliate view.
- Register the plugin and its views.
- Translate the screens to english.
- Translate the screens to persian.
- Link the profile's affiliation to its screen.
- Show the program as cards with sparklines.
- Put the program cards above the list.
- Add the program cards to the dashboard.
- Translate the cards to english.
- Translate the cards to persian.
- Add the plain profile section title.
- Add the persian profile section title.
- Give the profile section as a summary.
- Add empty-list messages.
- Group, date and tidy the panel screens.
- Build the affiliate page from the shared components.

### Changed

- Share the overview columns as withTotals().
- Hand user-management the summary directly.

## [0.0.35] - 2026-11-03

### Fixed

- The affiliation menu no longer fails for a user carrying a relation another
  package cached with `setRelation()` (the access package's `accessRoles`):
  it loads the affiliate instead of refreshing the whole model.

## [0.0.34] - 2026-11-03

### Fixed

- A referrer who blocked the bot no longer costs a new member their signup bonus:
  each bonus is its own action. Credits and their records commit together, and
  a notice that cannot be delivered no longer fails anything. Needs essence with
  `isolated()`.

## [0.0.33] - 2026-11-01

### Added

- `/affiliation` runs in groups too, and answers there with a link to the
  private chat rather than show earnings. Needs essence's `continueInPrivate()`.

### Fixed

- Someone first met in a group counts as new when they arrive by referral link.

## [0.0.32] - 2026-11-01

### Added

- `lang/<locale>/abilities.php` labels this package's Gate abilities for
  essence's `abilities()` catalog, so a roles screen (tbe-access) shows
  them by name.

## [0.0.31] - 2026-10-31

### Fixed

- Requires essence 0.19.0-beta.2 or later; 0.19.0-beta.1 still had the
  permission API.

## [0.0.30] - 2026-10-31

### Changed

- Requires essence 0.19. The admin handlers name a Laravel Gate ability
  (`affiliates.manage`) instead of a permission, and nothing is registered with essence
  any more: the app's Gate decides who holds it. Essence no longer lets the
  bot owner through by default, so an app grants these with `Gate::before`
  or `Gate::define`.

### Removed

- The permission labels (`lang/*/permissions.php`).

## [0.0.29] - 2026-10-12

### Changed

- Requires essence 0.18. A flow's state is read and written on the webhook's
  conversation (`wHook()->conversation()`) instead of the user, so it stays
  in the chat it started in.

## [0.0.28] - 2026-10-12

Requires essence 0.17, which replaces power levels with named permissions.

### Changed

- The admin screens ask for `affiliates.manage` instead of the admin power level.
  The names are registered with essence's permission registry, so a role
  package can grant them separately.

## [0.0.27] - 2026-10-01

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

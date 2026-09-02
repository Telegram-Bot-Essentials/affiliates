# Telegram Bot Essentials — Affiliates

[![Latest Version](https://img.shields.io/packagist/v/telegram-bot-essentials/affiliates.svg)](https://packagist.org/packages/telegram-bot-essentials/affiliates)
[![tests](https://github.com/Telegram-Bot-Essentials/affiliates/actions/workflows/tests.yml/badge.svg)](https://github.com/Telegram-Bot-Essentials/affiliates/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A referral program for the
[`telegram-bot-essentials/essence`](https://github.com/Telegram-Bot-Essentials/essence)
ecosystem. Every user gets a shareable deep link; signups through it are tracked; both the
referrer and (optionally) the new user can be paid a signup bonus; and referrers earn an
ongoing commission on their referred users' purchases. Bonuses and commissions are paid
straight into [`user-wallet`](https://github.com/Telegram-Bot-Essentials/user-wallet).

Depends on essence's deep-link handling,
[`settings`](https://github.com/Telegram-Bot-Essentials/settings),
[`billing`](https://github.com/Telegram-Bot-Essentials/billing) events, and
[`user-wallet`](https://github.com/Telegram-Bot-Essentials/user-wallet).

## Installation

```bash
composer require telegram-bot-essentials/affiliates
php artisan migrate
```

Everything is configured per-bot through [Settings](https://github.com/Telegram-Bot-Essentials/settings):

| Setting | Type | Default |
|---|---|---|
| `affiliates.affiliates_status` | `CHECKBOX` | `false` |
| `affiliates.allow_existing_users` | `CHECKBOX` | `false` |
| `affiliates.share_percentage` | `NUMBER` | `10` |
| `affiliates.referrer_signup_bonus` | `NUMBER` | `0` |
| `affiliates.referred_signup_bonus` | `NUMBER` | `0` |
| `affiliates.share_tagline` | `TEXT` | — |

## How it works

- Each user's affiliate menu builds `https://t.me/{bot}?start={referral_code}`.
- A new user opening that link fires essence's `BotDeepLinkReceived`; the package creates a
  `Referral` and pays the signup bonuses.
- Attribution is **first-touch-wins** (a `unique(bot_id, bot_user_id)` constraint) and, by
  default, **new users only**. Self-referral is rejected.
- Every paid Billing invoice from a referred user credits the referrer a percentage
  (rounded down, wallet top-ups excluded). A later invoice revocation claws it back.

Commission listeners are queued (queue: `billing`) and restore the webhook context first,
since invoice events can fire from a gateway callback with no "current user".

## Documentation

Full documentation — the attribution rules, commission math, data model, and member UI —
lives on the Telegram Bot Essentials documentation site under **Modules → Affiliates**.

## License

[MIT](LICENSE).

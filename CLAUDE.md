# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Laravel 12 test-task repo (`mastapp/test-task-referrals`): a minimal referral-program API. A master (`Master`) refers another master via a referral code; when the referred master makes their first real payment, the referrer earns a reward. The task is to implement three API routes in [routes/api.php](routes/api.php); the domain logic they must expose already exists in the service/observer/models — see Architecture below.

Task instructions (Russian) are in [README.md](README.md). Key constraints from it:
- Auth is stubbed: the current master comes from an `X-Master-Id` header, resolved by [ResolveCurrentMaster](app/Http/Middleware/ResolveCurrentMaster.php) into `$request->attributes->get('current_master')`. `X-Master-Id: 1` is Masha (seeded with referral code `MASHA10`).
- Two business rules are deliberately *not* spelled out in the README and must be read from the code instead of assumed from "how referral programs usually work": **when a referral counts as rewarded**, and **how the reward amount is calculated**. Both live in [PaymentObserver](app/Observers/PaymentObserver.php) and [ReferralService](app/Services/Referral/ReferralService.php) — see Architecture.

## Commands

Local PHP 8.2+/Composer, or via Docker (see README.md step-by-step Docker invocations). Locally:

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Common dev commands:

```bash
php artisan migrate:fresh --seed   # rebuild DB from scratch
php artisan route:list             # inspect routes
php artisan tinker                 # poke at data in a REPL
vendor/bin/phpunit                 # run tests
vendor/bin/phpunit --filter=testName   # run a single test
vendor/bin/phpunit tests/Feature/SomeTest.php  # run one test file
```

There are no lint/format tooling configs (no Pint/PHPStan config present) and no `tests/Feature` directory yet despite `phpunit.xml` pointing at it — add tests there if you write any.

## Architecture

Three tables exist via migrations: `masters`, `payments`, `referrals` (see [database/migrations](database/migrations)). A fourth, `referral_earnings`, is used by the [ReferralEarning](app/Models/ReferralEarning.php) model but **has no migration** — its schema must be inferred from the model's `$fillable`/`$casts` and from how [PaymentObserver](app/Observers/PaymentObserver.php) constructs rows, and a migration written for it before earnings can be persisted.

Data flow for a referral reward:
1. A master is attached to a referrer via [ReferralService::registerReferral()](app/Services/Referral/ReferralService.php), which creates a `Referral` row (`status = pending`) keyed by `referred_master_id` (so re-attaching is a no-op via `firstOrCreate`), and refuses self-referral or an unknown code (returns `null`).
2. Payments are created via the `Payment` model (not a payments endpoint — routes here don't create payments, they only read/derive state from existing ones, per the seeder's payment history).
3. [PaymentObserver::created()](app/Observers/PaymentObserver.php) fires on every new `Payment` (registered in [AppServiceProvider::boot()](app/Providers/AppServiceProvider.php)). It only acts on *monetary* payments (`Payment::isMonetary()` — type `card`/`sbp` with `amount > 0`; `promo`/`trial` are always `amount = 0`), only when the referred master has a `pending` referral, and only on that master's **first** monetary payment (counts existing monetary payments via `Payment::scopeMonetary()`; bails if count `> 1`). On qualifying, it creates a `ReferralEarning` row and flips the `Referral` to `status = rewarded`.
4. Reward amount is `ReferralService::rewardAmount()`: `round(paymentAmount * percent)` where `percent` comes from `config('referral.percent')` (env `REFERRAL_PERCENT`, default 10) — note this is **not** divided by 100, so read it exactly as coded rather than assuming a conventional percentage calculation.

The routes to implement (`POST /api/referrals/attach`, `GET /api/referrals/my`, `GET /api/referrals/earnings`) should be built on top of this existing service/model layer rather than re-deriving the business rules inline.

Seed data ([database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)) is created through the `Payment`/`Referral` models (not raw inserts), so `PaymentObserver` runs during seeding exactly as it would in production — useful for reasoning about expected output when testing routes manually.

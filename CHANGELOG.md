# Changelog

All notable changes to `veldora-connect` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v0.7.1] - 2026-09-15

### Added
- `Stripe` Facade (`Veldora\Connect\Stripe\Facades\Stripe`) for static access to all Stripe services.
- `stripe()` global helper function for ergonomic access inside Veldora applications.

### Fixed
- Root `composer.json` type changed from `project` to `library` so Packagist registers it as an installable package.
- Root `composer.json` now declares proper `require` (PHP, `veldora/framework`, `stripe/stripe-php`) and autoloading.
- `StripeServiceProvider` container calls updated to use `get()` (fully compatible with Veldora 0.6.x and 0.7.x).
- `config/stripe.php` now supports fallback env keys: `STRIPE_SECRET` / `STRIPE_KEY` / `STRIPE_CURRENCY`.
- Root package uses `replace: veldora/connect-stripe` so installing `veldora/connect` satisfies any dependency on `veldora/connect-stripe`.

---

## [v0.7.0] - 2026-09-12


### Added
- **Official Stripe Integration (`veldora/connect-stripe`)**:
  - Full support for Veldora Framework `^0.6.0` and `^0.7.0`.
  - `StripeClient` wrapper with auto-configuration from `.env` and `config/stripe.php`.
  - `StripeServiceProvider` with automatic container binding (`stripe` and `StripeClient::class`).
  - `Stripe` Facade for convenient static access (`Stripe::checkout()`, `Stripe::paymentIntents()`, `Stripe::customers()`, etc.).
  - Checkout Session creation and retrieval helpers.
  - PaymentIntent creation, confirmation, and capture helpers.
  - Customer management (create, retrieve, update, delete).
  - Webhook event verification and parsing (`Stripe::webhook()->constructEvent()`).
  - Webhook dispatching with Veldora event system integration (`StripeWebhookHandled`, `StripeWebhookFailed`).
  - Robust error handling wrapping Stripe API exceptions with detailed context.
  - Complete test suite with 33 unit and integration tests passing.

### Changed
- Monorepo structure configured with first-party Stripe release for v0.7.0.
- Roadmap laid out for future Connect integrations (SSLCommerz, Resend, S3, Sentry).

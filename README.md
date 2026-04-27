# TransactX Lite

A fintech wallet API built with Laravel 13. Designed to demonstrate financial correctness, clean architecture, and production awareness.

Every naira is tracked through a double-entry ledger. No balance is ever stored directly on a user record.

---

## What's been built

### Authentication
- User registration with automatic wallet provisioning (atomic — no orphaned users)
- Token-based login via Laravel Sanctum with single-session enforcement (old tokens revoked on login)
- Logout (revokes current token)
- Password hashing via Laravel's built-in `hashed` cast

### Virtual Account Provisioning
- On registration, a queued job (`ProvisionVirtualAccountJob`) is dispatched to create a dedicated NUBAN via Paystack
- Idempotency guard — re-running the job on an existing user is a no-op
- Stub mode: when `PAYSTACK_STUB=true` (or no secret key is set), a deterministic fake account is returned — no real Paystack credentials needed for local development
- Retry logic: 3 attempts with exponential backoff (60s, 120s, 180s)

### API Documentation
- Swagger/OpenAPI docs auto-generated via `l5-swagger`
- All endpoints annotated with request/response schemas

### Database
- `users` — standard auth table
- `wallets` — one per user, no balance column (balance is computed from the ledger)
- `virtual_accounts` — stores NUBAN, bank name, account name, and Paystack reference

---

## What's not built yet

### Double-Entry Ledger (next up)
- `ledger_accounts`, `ledger_transactions`, `ledger_entries` tables and models
- `LedgerService` with `recordDeposit()` and `getBalance()` methods
- Platform-level account seeder (Platform Bank Account, Revenue Account)
- Balance computed as `sum(credits) - sum(debits)` — never stored directly

### Webhook Handler
- `POST /api/webhooks/paystack` endpoint (stub exists, returns placeholder)
- HMAC SHA512 signature verification
- Idempotency via `processed_webhooks` table
- `ProcessDepositJob` to write ledger entries asynchronously

### Wallet Balance & Transaction History APIs
- `GET /wallet` — returns balance + virtual account info (stub exists, returns placeholder)
- `GET /transactions` — paginated ledger entries (stub exists, returns placeholder)

### Logging & Audit Trail
- Dedicated `transactions` log channel for financial events
- Audit log on every deposit: `user_id`, `amount`, `reference`, `timestamp`

### Deployment
- Nginx config
- Supervisor config for queue worker
- Production `.env` template

---

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 |
| Auth | Laravel Sanctum |
| Payment Provider | Paystack (DVA) |
| Queue | Sync (dev) / Redis (prod) |
| Database | MySQL |
| API Docs | l5-swagger (OpenAPI 3) |

---

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

For local development without Paystack credentials, set `PAYSTACK_STUB=true` in `.env` — stub mode returns a deterministic fake account.

---

## API endpoints

| Method | Path | Auth | Status |
|---|---|---|---|
| POST | `/api/auth/register` | No | ✅ Done |
| POST | `/api/auth/login` | No | ✅ Done |
| POST | `/api/auth/logout` | Bearer | ✅ Done |
| GET | `/api/wallet` | Bearer | 🔲 Pending |
| GET | `/api/transactions` | Bearer | 🔲 Pending |
| POST | `/api/webhooks/paystack` | Signature | 🔲 Pending |

---

## Running tests

```bash
php artisan test
```

Current coverage: authentication flow + virtual account job dispatch.

---

## Design decisions worth noting

- **No balance column** — wallet balance is always derived from ledger entries. This prevents silent corruption and makes every naira auditable.
- **Async provisioning** — the Paystack call is dispatched as a queued job so a Paystack outage never breaks registration.
- **Stub mode** — the entire Paystack integration can run without real credentials, making local dev and CI straightforward.
- **Kobo everywhere** — all monetary amounts are stored as integers in kobo (₦1 = 100 kobo). No floats, no rounding errors.

# Tribal DMV Platform (Laravel + Inertia Vue 3)

## Stack
- Laravel 12 API + Inertia server-side routing
- Vue 3 frontend (no Vue Router)
- Tailwind CSS
- Sanctum token auth for member portal API calls

## Modes: Demo vs Live Data

This project supports two seed modes:

- `demo`: rich seeded dataset for demos
- `live`: minimal production-safe bootstrap data

### Mode switch commands

```bash
php artisan tdmv:mode demo --fresh
php artisan tdmv:mode live --fresh
```

Without `--fresh`, the command only updates `.env` (`TDMV_SEED_MODE`) and clears config.

## Local Setup

```bash
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

## Demo Credentials

- Admin: `admin@tribe.gov` / `password`
- Member: `john@example.com` / `password`

## Smoke Testing

Run the targeted platform smoke suite:

```bash
php artisan tdmv:smoke --fresh
```

Run all tests:

```bash
php artisan test
```

## Implemented portal flows

- Service selection and application creation
- Requirements checklist
- Document upload and deletion
- Review + payment + submission
- Application status and timeline
- Notification center + preferences
- Support page (FAQ + office locations)
- Admin dashboard with queue actions
- Phase 2A: business/fleet, insurance/compliance, benefits/placards
- Phase 2B: household management and appointment scheduling

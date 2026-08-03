# DICT Region 2 — Travel Order System

Internal workflow app for requesting, approving, and issuing official Travel Orders for DICT Region 2 staff.

## Stack

- PHP 8.2+ / Laravel 12
- Filament 3 admin panel at `/DICT`
- MySQL
- Vite + Tailwind CSS 4
- DomPDF for completed TO PDFs

## Roles

| Role | Access |
|------|--------|
| `employee` | Create/submit own travel orders |
| `admin` | Approvals (when assigned), broader visibility |
| `hr` | TO code override during approval |
| `super_admin` | Settings, patch notes, full authority |

Panel login is restricted to `@dict.gov.ph` emails.

## Local setup

```bash
composer install
cp .env.example .env   # configure DB, mail, APP_URL
php artisan key:generate
php artisan migrate
# Optional seed (set SEED_USER_PASSWORD in .env first):
php artisan db:seed
npm install && npm run build
php artisan serve
php artisan queue:work   # notifications / mail
```

Open `http://localhost:8000/DICT`.

### Important env keys

- `SEED_USER_PASSWORD` — shared password used when seeding users (required for usable seeded accounts)
- `TO_CODE_SEQUENCE_START` — legacy TO number offset when no codes exist for the year (default `149`)

## Workflow

Draft → Submit → multi-step approvals (per-user Travel Workflow) → Completed PDF.

Statuses: `DRAFT`, `PENDING`, `COMPLETED`, `REJECTED`, `CANCELLED`, `FOR_REVISION`.

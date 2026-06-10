# Tsekap V2 — Laravel REST API

Backend REST API for the **Tsekap V2** health risk assessment and patient profiling system, developed for the Department of Health (DOH) Community Health program. Built with Laravel 11 and secured via Laravel Sanctum token-based authentication.

---

## Overview

Tsekap V2 is a digital health platform used by community health workers (CHWs) and facility-level staff to:

- Profile patients and record demographic/health data
- Conduct and track health risk assessments (PhilPEN and PCH-RAT tools)
- Record patient injury form data (pre-admission)
- Manage population totals and coverage targets per municipality/barangay
- Generate analytics and reports across health facilities

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 11 |
| PHP | 8.2+ |
| Auth | Laravel Sanctum |
| Database | MySQL |

---

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+
- Node.js (for development tooling)

---

## Getting Started

```bash
# Clone the repository
git clone <repo-url>
cd Tsekap-V2-Laravel-API

# Install dependencies
composer install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then run migrations and seeders
php artisan migrate
php artisan db:seed

# Start the development server
php artisan serve
```

The API will be available at `http://localhost:8000/api/v2`.

---

## Authentication

All protected routes require a Bearer token obtained from the login endpoint.

```
POST /api/v2/login
POST /api/v2/register
```

Include the token in subsequent requests:
```
Authorization: Bearer <token>
```

---

## API Reference

All routes are prefixed with `/api/v2`.

### Auth

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | Authenticate and receive access token |
| POST | `/register` | Self-register a new user account |

### Miscellaneous / Address Data

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/misc/get-all-facility` | List all health facilities |
| GET | `/misc/get-countries` | List countries |
| GET | `/misc/get-regions` | List regions |
| GET | `/misc/get-provinces` | List provinces |
| GET | `/misc/get-muncities` | List municipalities/cities |
| GET | `/misc/get-barangays` | List barangays |
| GET | `/misc/get-all-citizenships` | List citizenships |
| GET | `/misc/get-all-religions` | List religions |
| GET | `/misc/get-mobile-version` | Get current mobile app version |

### User & Facility Management _(auth required)_

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/facility/add-user-facility` | Assign user to a health facility |
| GET | `/admin/list-users` | List all users |
| GET | `/admin/list-users-by-facility` | List users by facility |
| GET | `/admin/list-unverified-users` | List pending verification users |
| POST | `/admin/verify-user` | Verify a user account |
| POST | `/admin/unverify-user` | Revoke user verification |
| POST | `/admin/register-user` | Admin-register a new user |
| POST | `/admin/reset-user-password` | Reset a user's password |

### Health Risk Assessment Forms _(auth required)_

#### PhilPEN Risk Assessment
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/forms/philpen/...` | Create / update PhilPEN risk assessment entries |

#### PCH Risk Assessment Tool (PCH-RAT)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/forms/pch/...` | Create / update PCH-RAT entries |

#### Patient Injury Form
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/forms/injury/...` | Submit patient injury / pre-admission data |

### Analytics _(auth required)_

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/analytics/admin-analytics/get-number-of-entries-by-user-per-facility` | Entry counts per user per facility |
| GET | `/analytics/data/general_analytics/get-age-brackets` | Age bracket breakdown |
| GET | `/analytics/data/philpen/...` | PhilPEN-specific analytics |
| GET | `/analytics/data/pchrat/...` | PCH-RAT-specific analytics |

### Profiling Target Setting _(auth required)_

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/analytics/target/muncity/...` | Manage coverage targets per municipality |
| GET/POST | `/analytics/target/barangay/...` | Manage coverage targets per barangay |
| GET/POST | `/analytics/population/muncity/...` | Manage population totals per municipality |
| GET/POST | `/analytics/population/barangay/...` | Manage population totals per barangay |

---

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── AuthController.php
│       ├── AdminController.php
│       └── TsekapV2/
│           ├── Forms/
│           │   ├── RiskAssessmentForm/      # PhilPEN
│           │   ├── PchRiskAssessmentForm/   # PCH-RAT
│           │   └── PatientInjuryForm/       # PATIENT INJURY FORM
│           ├── Analytics/
│           │   ├── DataRetrieval/           # Analytics endpoints
│           │   └── ProfilingTargetSetting/  # Population & targets
│           ├── Misc/                        # Address/lookup data
│           └── Websockets/                  # Push notifications
├── Models/
│   └── TsekapV2/                            # Eloquent models
database/
├── migrations/
└── seeders/
routes/
└── api.php
```

---

## Environment Variables

Key `.env` values to configure:

```env
APP_NAME=TsekapV2
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tsekap_v2
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost
```

---

## License

Internal use — Department of Health (DOH), Philippines.

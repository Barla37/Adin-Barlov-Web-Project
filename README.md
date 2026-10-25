# Barla Gym

Single-page web application for managing a gym: membership plans, trainers, group
classes and class bookings. Built for the IT 2001 Web Programming course
(International Burch University, 2026/2027).

> **Status: Milestone 1 (draft).** Static SPA frontend, project structure and ERD.
> The backend, database and API are added in the next milestones.

## Tech stack

- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5, Highcharts (hash-based SPA router)
- **Backend:** PHP 8.2+, FlightPHP, PDO (Milestone 2+)
- **Database:** MySQL / MariaDB
- **Auth:** JWT (Milestone 4)
- **Docs:** OpenAPI / Swagger (Milestone 3)

## Entities and relationships

| Entity | Description |
|---|---|
| `users` | Registered people (admins and members) |
| `membership_plans` | Basic, Premium, VIP... with price and duration |
| `memberships` | A user's purchased plan with start/end date |
| `trainers` | Gym trainers |
| `classes` | Scheduled group classes taught by a trainer |
| `bookings` | A user's booking for a class |

See the full diagram in [docs/ERD.md](docs/ERD.md).

## Roles and test accounts

| Role | Can do | Email | Password |
|---|---|---|---|
| `admin` | Full CRUD on all entities, dashboard with statistics | _added in Milestone 2/4_ | _added in Milestone 2/4_ |
| `member` | Browse classes/trainers/plans, book classes, edit own profile | _added in Milestone 2/4_ | _added in Milestone 2/4_ |

## External API

**wger Exercise Database** (https://wger.de/api/v2/). The *Exercises* page and the
"Exercise of the day" card on the dashboard show exercises with muscle groups and
equipment. The frontend calls our own backend endpoint, which calls wger with a
timeout and logs failures; if wger is down the app shows a friendly message and
keeps working. _(Implemented in Milestone 3.)_

## Running locally

### Milestone 1 (frontend only)

1. Install XAMPP and start Apache.
2. Put the project in `htdocs/` (e.g. `htdocs/gym-app`).
3. Open `http://localhost/gym-app/frontend/`.

The frontend loads view templates with `fetch`, so it must be opened through
Apache (not by double-clicking `index.html`).

### Later milestones

1. Start Apache and MySQL in XAMPP.
2. Import `database/gym_app.sql` in phpMyAdmin _(Milestone 2)_.
3. `cp backend/config/config.example.php backend/config/config.php` and fill in
   DB credentials, JWT secret and API settings.
4. `cd backend && composer install`

## API documentation

Swagger UI link: _added in Milestone 3_

## Deployed application

Live link: _added in Milestone 5_

## Project structure

```
gym-app/
├── AGENTS.md
├── README.md
├── docs/            ERD.md, AI_LOG.md
├── database/        .sql schema + sample data (Milestone 2)
├── backend/
│   ├── config/      config.example.php (config.php is git-ignored)
│   ├── logs/        info.log, error.log (git-ignored)
│   └── rest/
│       ├── routes/
│       ├── services/
│       ├── dao/
│       └── middleware/
└── frontend/
    ├── index.html
    ├── css/
    ├── js/
    ├── views/       HTML templates for each SPA page
    └── assets/
```

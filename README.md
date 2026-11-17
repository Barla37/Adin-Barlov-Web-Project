# Barla Gym

Single-page web application for managing a gym: membership plans, trainers, group
classes and class bookings. Built for the IT 2001 Web Programming course
(International Burch University, 2026/2027).

> **Status: Milestone 2.** Static SPA frontend (M1), MySQL schema with sample data and
> the DAO layer (M2). Services, REST API and Swagger follow in the next milestones.

## Tech stack

- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5, Highcharts (hash-based SPA router)
- **Backend:** PHP 8.2+, FlightPHP, PDO (DAO layer, Milestone 2)
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
| `admin` | Full CRUD on all entities, dashboard with statistics | `admin@example.com` | `Admin123!` |
| `member` | Browse classes/trainers/plans, book classes, edit own profile | `ana@example.com` | `Member123!` |

Other sample members (`nermin@`, `selma@`, `kenan@`, `mia@example.com`) use the same
password `Member123!`. Passwords are stored as bcrypt hashes. Login itself is
implemented in Milestone 4; for now these accounts exist in the database.

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

### Milestone 2 (database and DAO layer)

1. Start Apache and MySQL in XAMPP.
2. Import the database: phpMyAdmin → *Import* → choose `database/gym_app.sql` → *Go*
   (or `mysql -u root -p < database/gym_app.sql`). The script creates the `gym_app`
   database and can be re-run at any time to reset the sample data.
3. Create your local config (it is git-ignored, never commit it):
   `cp backend/config/config.example.php backend/config/config.php`
   and set `DB_USER` / `DB_PASSWORD` if they differ from XAMPP defaults (`root` / empty).
4. Test the DAOs: `php backend/rest/dao/test.php`. It runs inside a transaction that is
   rolled back, so the sample data stays intact. Requires MySQL 8.0.16+ / MariaDB 10.2.1+
   (needed for CHECK constraints).

### Later milestones

`cd backend && composer install` (FlightPHP and other libraries, Milestone 3+).

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
├── database/        gym_app.sql (schema + sample data)
├── backend/
│   ├── config/      config.example.php (config.php is git-ignored)
│   ├── logs/        info.log, error.log (git-ignored)
│   └── rest/
│       ├── routes/
│       ├── services/
│       ├── dao/     BaseDao + one DAO per entity, test.php
│       └── middleware/
└── frontend/
    ├── index.html
    ├── css/
    ├── js/
    ├── views/       HTML templates for each SPA page
    └── assets/
```

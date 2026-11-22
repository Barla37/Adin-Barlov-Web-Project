# Barla Gym

Single-page web application for managing a gym: membership plans, trainers, group
classes and class bookings. Built for the IT 2001 Web Programming course
(International Burch University, 2026/2027).

> **Status: Milestone 3.** Static SPA frontend (M1), MySQL schema and DAO layer (M2),
> service layer, REST API with FlightPHP, OpenAPI/Swagger documentation and the external
> wger API (M3). Authentication, connecting the frontend and deployment follow in M4 and M5.

## Tech stack

- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5, Highcharts (hash-based SPA router)
- **Backend:** PHP 8.2+, FlightPHP, PDO (DAO layer M2, services and routes M3)
- **Database:** MySQL / MariaDB
- **Auth:** JWT (Milestone 4)
- **Docs:** OpenAPI / Swagger (swagger-php, Milestone 3)

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
keeps working. The backend endpoint is `GET /exercises?limit=12&offset=0` (implemented in
Milestone 3; the frontend is connected to it in Milestone 4).

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

### Milestone 3 (services, REST API and Swagger)

1. Do the Milestone 2 steps first (database imported, `backend/config/config.php` created).
   Enable the `curl` and `pdo_mysql` extensions in `php.ini` if they are not on (XAMPP: on by default).
2. Install the libraries (FlightPHP, swagger-php):
   `cd backend && composer install` (creates the git-ignored `vendor/` folder).
3. Start Apache in XAMPP and open `http://localhost/<project-folder>/backend/` (the `.htaccess` sends every
   request to `index.php`; `mod_rewrite` must be on, which is the XAMPP default).
4. Try it: `GET /trainers`, `GET /classes`, `GET /exercises?limit=3` (see Swagger below).
5. Test the services: `cd backend && php rest/services/test.php`. The script tries to break every
   business rule and deletes what it created, so it can be run repeatedly.

If the wger API needs a key, put it in `backend/config/config.php` (`WGER_API_KEY`).

## API documentation

Swagger UI (local): `http://localhost/<project-folder>/backend/public/v1/docs/`
Swagger link on the deployed server: _added in Milestone 5_

### Endpoints

| Resource | Endpoints |
|---|---|
| Users | `GET/POST /users`, `GET/PUT/PATCH/DELETE /users/{id}`, `GET /users/{id}/memberships`, `GET /users/{id}/bookings` |
| Membership plans | `GET/POST /membership-plans`, `GET/PUT/PATCH/DELETE /membership-plans/{id}` |
| Memberships | `GET/POST /memberships`, `GET/PUT/PATCH/DELETE /memberships/{id}` |
| Trainers | `GET/POST /trainers`, `GET/PUT/PATCH/DELETE /trainers/{id}` |
| Classes | `GET/POST /classes`, `GET/PUT/PATCH/DELETE /classes/{id}`, `GET /classes/{id}/bookings` |
| Bookings | `GET/POST /bookings` (filters `?user_id=`, `?class_id=`), `GET/PUT/PATCH/DELETE /bookings/{id}` |
| Exercises (wger) | `GET /exercises?limit=&offset=` |

Every answer is JSON, errors look like `{"error": "message"}`. Status codes: 200, 201, 400 (invalid
data or a broken business rule), 404, 500, and 503 when the external wger API is not available.
Login and role checks (401/403) are added in Milestone 4.

### Business rules (service layer)

- **Users:** unique e-mail (case-insensitive), strong password stored only as a bcrypt hash, the last admin cannot be deleted or demoted.
- **Plans:** unique name, price >= 0, a plan used by memberships cannot be deleted.
- **Memberships:** end date not before start date, no two overlapping active memberships for one user; the end date defaults to start + plan duration.
- **Trainers:** a trainer who still teaches classes cannot be deleted.
- **Classes:** a new class must be in the future, no overlapping classes in one room, capacity cannot drop below the booked places, a class with bookings cannot be deleted.
- **Bookings:** needs an active membership whose plan includes classes, class must not have started and must have a free place, no double booking (a cancelled booking is re-confirmed), status can only change in a sensible order.

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
│   ├── index.php    front controller (services, error handler, routes)
│   ├── .htaccess    sends every request to index.php
│   ├── composer.json
│   ├── config/      config.example.php (config.php is git-ignored)
│   ├── logs/        info.log, error.log (git-ignored)
│   ├── utils/       exceptions.php, Validator.php, Logger.php
│   ├── public/v1/docs/  Swagger UI (index.html, swagger.php, doc_setup.php, paths/)
│   └── rest/
│       ├── routes/      one file per resource (HTTP in, JSON out)
│       ├── services/    BaseService + one service per entity, ExerciseService (wger), test.php
│       ├── dao/         BaseDao + one DAO per entity, test.php
│       └── middleware/  (Milestone 4)
└── frontend/
    ├── index.html
    ├── css/
    ├── js/
    ├── views/       HTML templates for each SPA page
    └── assets/
```

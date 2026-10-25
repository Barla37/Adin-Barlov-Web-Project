# AGENTS.md

Context and rules for AI coding assistants working in this repository.

## Project description

Barla Gym: a single-page web application for managing a gym (membership plans,
trainers, group classes, bookings). University project for IT 2001 Web Programming
(IBU). Individual work; the author must be able to explain every line of code.

## Technology stack

- Backend: PHP 8.2+, FlightPHP, PDO, MySQL/MariaDB, firebase/php-jwt(planned, milestone 4)
- Frontend: plain HTML, CSS, JavaScript (no frameworks), Bootstrap 5, Highcharts, DataTables(planned)
- API documentation: OpenAPI (Swagger UI)
- Local environment: XAMPP

## Architecture rules

1. **No mixing of frontend and backend code.** Backend is pure PHP (JSON only, no HTML).
   Frontend is only HTML/CSS/JS.
2. **Three layers in the backend:** `routes` → `services` → `dao`.
   - Routes: HTTP in, JSON out. No SQL, no business rules.
   - Services: business logic and validation. The only layer that calls DAOs.
   - DAOs: PDO with prepared statements only. No business rules, no `Flight::json`.
3. All DAOs extend a `BaseDao` with reusable CRUD methods.
4. Middleware (auth, role check, request logging, error handling) is registered in
   FlightPHP and reused, never copied into routes.
5. Frontend is a SPA with a hash router; templates live in `frontend/views/`.
   From Milestone 5, only frontend service files call the backend (views contain no AJAX).
6. Secrets (DB credentials, JWT secret, API keys) live in `backend/config/config.php`,
   which is git-ignored. Only `config.example.php` is committed.
7. Logs: `backend/logs/info.log` and `backend/logs/error.log`. Errors returned to the
   client never include stack traces, SQL or file paths.
8. Return correct HTTP status codes: 200, 201, 400, 401, 403, 404, 500.

## Folder structure

```
backend/config  backend/logs  backend/rest/{routes,services,dao,middleware}
frontend/{css,js,views,assets}
database/  docs/
```

## Naming conventions

- PHP classes: `PascalCase` (`BookingService`, `BookingDao`, `BookingRoutes`); files match class names.
- PHP methods/variables: `camelCase`. Start every file with `declare(strict_types=1);`.
- DB tables: plural `snake_case` (`membership_plans`); columns `snake_case`; PK `id`; FK `<entity>_id`.
- API routes: plural nouns, lowercase (`GET /bookings`, `GET /bookings/@id`).
- Frontend files: `kebab-case` (`my-bookings.html`); JS functions `camelCase`.
- Git: one branch per milestone (`milestone1` ... `milestone5`); never merge a milestone branch into `main` yourself.

## Rules the AI must follow

- Do not add features, libraries or files that were not asked for.
- Use prepared statements for all SQL. Never concatenate user input into queries.
- Hash passwords with `password_hash`; never log passwords or tokens.
- Do not commit secrets or edit `config.php`.
- Keep code simple and commented where non-obvious; the author must understand it.
- Update `docs/AI_LOG.md` whenever AI is used for a milestone.

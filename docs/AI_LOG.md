# AI Log

## Milestone 1: Setup, static frontend, ERD

### AI tools used and for which parts

- Claude (claude.ai): proposed the gym topic, generated the initial folder structure,
  `.gitignore`, `config.example.php`, the static SPA (router, views, CSS), the draft ERD,
  README draft and AGENTS.md.


### Cases where the AI was wrong or produced poor code

1. Wrong diagnosis of a mobile layout problem
- What was wrong: Claude's own automated check reported horizontal scrolling on the admin pages at mobile width, and Claude "fixed" it by adding `flex-wrap` to the page headers. That was the wrong diagnosis: the real cause was that Claude's test environment could not load the Bootstrap CDN, so `.table-responsive` had no styles.
- How I noticed: Claude reported it itself after its second test run still showed the scroll. I then checked it myself in my browser.
- How I fixed it: Tested with Bootstrap loaded, confirmed the table scrolls inside its container, kept the flex-wrap change.

2. AGENTS.md described things that were not in the project yet
- What was wrong: AGENTS.md listed `firebase/php-jwt` and DataTables in the tech stack, although neither is used in Milestone 1.
- How I noticed: when I re-read AGENTS.md against the actual files.
- How I fixed it: Marked them as "planned (Milestone 4 / later)" in AGENTS.md.


## Milestone 2: Database and DAO layer

### AI tools used and for which parts

- Claude (claude.ai / Claude Code): drafted `database/gym_app.sql` (6 tables, constraints, sample data with bcrypt hashes), the `Config` class, `BaseDao`, the six DAO classes and `test.php`, following the Lab 03 DAO structure; updated README and AGENTS.md.
- Claude could not run MySQL in its environment, so it tested the DAOs against SQLite with a throwaway copy of the schema. The real test on MySQL (XAMPP) is mine.

### Most important prompts

TODO (write your own, 2-3 prompts you actually used, e.g. "build everything for milestone 2 using the Lab 03 DAO document, my M1 project and the requirements PDF").

### Cases where the AI was wrong or produced poor code

TODO: fill in at least 2 real cases after you import the SQL and run `php backend/rest/dao/test.php` on XAMPP. Use what really happened (error message, how you noticed, how you fixed it). Known things already worth checking:
- AGENTS.md from Milestone 1 said methods are `camelCase` and every file needs `strict_types`, which contradicts the course lab code (snake_case). I changed the rules to match the labs.
- Claude's tests ran on SQLite, not MySQL, so SQL syntax differences were not verified until I ran the import myself.

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

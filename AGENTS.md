# AGENTS.md

## Stack and code boundaries
- This is a Laravel 12 app with Livewire/Flux UI and Volt auth/settings routes. Main route wiring lives in `routes/web.php`; auth flows are in `routes/auth.php`.
- Product and inventory screens are class-based Livewire components under `app/Livewire/**` with paired Blade views under `resources/views/livewire/**`.
- Shared business logic lives in `app/Services/**`. When behavior is reused, transactional, or audit-related, check the service layer before adding logic directly to a Livewire component.
- Vite is only wiring `resources/css/app.css` and `resources/js/app.js` (`vite.config.js`). `resources/js/app.js` is currently empty, so most UI behavior is server-driven through Blade/Livewire. Vite refresh watches `resources/views/**/*`.

## Commands worth using exactly
- `composer dev` — starts the local app loop: `php artisan serve`, `php artisan queue:listen --tries=1`, and `npm run dev`.
- `npm run dev` — Vite dev server only.
- `npm run build` — production asset build; CI runs this before PHPUnit.
- `vendor/bin/pint` — PHP formatter/lint step used by CI.
- `./vendor/bin/phpunit` — test runner used by CI.

## Verification flow
- For app changes, mirror CI order instead of guessing: `npm i` → `touch database/database.sqlite` → `composer install --no-interaction --prefer-dist --optimize-autoloader` → `cp .env.example .env` → `php artisan key:generate` → `npm run build` → `./vendor/bin/phpunit`.
- PHPUnit is configured for `Unit` and `Feature` suites in `phpunit.xml`; tests run on SQLite with `DB_DATABASE=:memory:` during PHPUnit.
- There is no verified JS lint, JS test, or typecheck command in this repo. Do not invent one in agent workflows.

## Repo-specific gotchas
- `docker-compose.yml` exposes MySQL/MariaDB on `${FORWARD_DB_PORT:-3306}`, but the custom image in `docker/mysql/Dockerfile` only initializes MariaDB and binds `0.0.0.0`; it does **not** consume `MYSQL_USER`, `MYSQL_PASSWORD`, or `MYSQL_DATABASE` to create accounts/databases the way the official image does. If remote tools get `Access denied`, check real DB users/grants instead of assuming Compose env created `admin/secret`.
- There are no repo-local agent instruction files besides this one: no existing `AGENTS.md`, `CLAUDE.md`, `.cursor` rules, `.github/copilot-instructions.md`, or `.opencode` config were present when this file was generated.

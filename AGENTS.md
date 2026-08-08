# Repository Guidelines

## Project Structure & Module Organization

The Laravel 12/PHP 8.2 application lives in `www/`. Modules under `www/app/Modules/<Domain>` follow `Application`, `Domain`, `Infrastructure`, and `Presentation` layers. Routes are in `www/routes`, templates and assets in `www/resources`, database files in `www/database`, and tests in `www/tests`. Docker configuration is under `.infrastructure/docker`; use the root `Makefile`.

## Build, Test, and Development Commands

- `make init`: build images, install Composer/npm dependencies, start services, and migrate/seed the database.
- `make up` / `make down`: start or remove the local stack.
- `make build`: rebuild the `web` and `worker` images.
- `make test`: run the Laravel test suite with an in-memory SQLite database.
- `make artisan CMD="migrate"`: run an Artisan command in the container.
- `make npm CMD="run dev"`: start Vite; use `run build` for production assets.
- `make composer CMD="install"`: run Composer in the application container.

## Coding Style & Naming Conventions

Follow `.editorconfig`: UTF-8, LF endings, final newline, four-space indentation (two spaces for YAML), and no trailing whitespace. Use PSR-4 namespaces and Laravel conventions: PascalCase classes, camelCase methods, snake_case database identifiers, and timestamped snake_case migration names. Keep domain logic in the relevant module instead of expanding controllers. Format PHP with `cd www && ./vendor/bin/pint` before submitting.

New or changed Blade interfaces must use approved layouts and shared components in `www/resources/views/components/ui`. Reuse or extend design-system primitives for buttons, fields, tables, switches, radios, and checkboxes; do not duplicate markup or create one-off patterns. Support Tailwind dark mode through semantic tokens. Use Tabler icons and download only each required SVG—never install or copy the full set.

## Testing Guidelines

Use PHPUnit 11 through Laravel. End test class names with `Test.php`, such as `TenantMrpPlanningTest.php`. Add unit tests for isolated services and feature tests for HTTP, tenancy, database, and authorization flows. Every behavior change should include regression coverage. Run `make test` before opening a pull request.

## Commit & Pull Request Guidelines

Use short imperative subjects with prefixes such as `feat:`, `fix:`, `refactor:`, `style:`, and `docs:`. Keep each commit scoped to one logical change. Pull requests should explain the problem and solution, identify migrations or configuration changes, link the issue, and report test results. Include screenshots for Blade/UI changes and note tenant, queue, or deployment impact.

## Security & Configuration Tips

Copy settings from `www/.env.example`; never commit `.env`, credentials, tokens, or production data. Review tenant scoping, authorization, and validation whenever changing web routes, queries, or background jobs.

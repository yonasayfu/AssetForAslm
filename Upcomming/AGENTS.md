# AGENTS.md

Scope: This file applies to the entire `Upcomming/` directory tree only.

## Mission
- Plan and document the upcoming project using the AssetTiger Laravel/Inertia/Vue boilerplate.
- Keep guidance actionable, minimal, and consistent with existing code style.

## Do
- Place all new planning docs under `Upcomming/`.
- Cross-link existing docs: `MyNewProjectRoadmap.md`, `MyNewDatabaseSchema.md`, `MyNewAppsidebar.md`.
- Use concise PRs/patches focused on a single concern.
- Prefer Laravel conventions (controllers, policies, services) and Spatie packages for RBAC and activity logging.
- For money fields, store integers in minor units with a separate `currency` column.

## Don’t
- Don’t modify or delete code outside `Upcomming/` without explicit approval.
- Don’t introduce vendor lock-in or nonstandard folder structures.
- Don’t include project-specific names from prior apps (keep generic).

## Style & Structure
- Markdown headings ≤ level 3; short lists and clear links.
- Keep bullets one line when possible; avoid deep nesting.
- Use present tense, active voice.

## Useful Commands (assumed defaults)
- Backend: `composer install`, `php artisan migrate --seed`, `php artisan serve`
- Frontend: `npm install`, `npm run dev`
- Tests: `phpunit` (backend), `npm run test` (frontend)

## Milestones & Tasks
- Maintain `Upcomming/ROADMAP.md` for milestones and `Upcomming/TASKS.md` for backlog.
- When adding tasks, include: summary, rationale, acceptance criteria, dependencies.

## Security
- Enforce policies for all domain actions (Asset, Maintenance, Audit, Warranty, Reservation, Lease).
- Use queues for heavy jobs (imports, reports, alerts); avoid synchronous processing.

## Output Expectations
- When generating code stubs, match Laravel 12 + Inertia + Vue 3 patterns.
- When uncertain, propose options in `PROJECT-OWNER.md` and request approval.


# Phase 0 Foundation Notes

## Dependency Audit (Updated)
- Added backend packages required for shared features:
  - `spatie/laravel-permission` – RBAC layer.
  - `laravel/sanctum` – API token support for future mobile integration.
  - `maatwebsite/excel` + `league/csv` – import/export pipeline.
  - `barryvdh/laravel-dompdf` – printable reports.
  - `spatie/laravel-data` – DTO/resource pattern to mirror Geraye services.
  - `tightenco/ziggy` – route helpers for Vue permission guards.
  - `laravel/reverb` – real-time transport for notifications/alerts.
- Frontend packages still to add when needed:
  - Dashboards/charts: `chart.js`, `vue-chartjs`, `date-fns`.
  - Calendars: `@fullcalendar/*`.
  - Reusable utilities: `lodash`, `mitt`, `ziggy-js`.
  - Messaging extras (deferred to boilerplate add-on): `vue3-emoji-picker`, WebSocket client (`laravel-echo`, `pusher-js`).

## Environment Template
- `.env.example` now mirrors Geraye defaults, including Reverb keys for future broadcasting.
- Keep `QUEUE_CONNECTION=database` for now; switch to `redis` once Redis is provisioned.
- When Sanctum is enabled, remember to add `SESSION_DOMAIN` and `SANCTUM_STATEFUL_DOMAINS` entries if SPA/mobile access is required.

## Coding Conventions (matching Geraye)
- **Architecture**: Controllers stay thin; use Services + Actions + DTOs (Spatie Data) for business logic.
- **Repositories**: Group data access per module under `App/Repositories`.
- **Policies & Permissions**: Define per module, policy auto-discovery enabled; apply `PermissionGuard` on Vue routes.
- **Testing**: Feature tests for endpoints, Pest for unit tests; mirror Geraye naming (`*FeatureTest.php`).
- **Queues & Jobs**: Long-running tasks (exports, notifications) must be queued; default to sync only for local dev.

## Next Steps Before Phase 1
- Install required frontend dependencies when the related phase begins (dashboard, calendar, messaging).
- Draft a coding standards snippet for contributors in `README.md` once Phase 1 layout work starts.

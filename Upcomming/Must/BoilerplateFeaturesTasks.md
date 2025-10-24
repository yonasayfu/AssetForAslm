# Boilerplate Features Task Tracker

Use this list to track the remaining polish items for the reusable Laravel/Inertia boilerplate. Everything already shipped (2FA, notifications, Mailpit ingestion, impersonation, etc.) stays documented elsewhere – only the follow-up work appears below.

## 1. Activity Logging & Audit UI

- [ ] Finalize “activity matrix” (which models/actions must log) and publish in `BoilerplateFeaturesPlan.md`.
- [ ] Expand `RecordsActivity` coverage to user/staff CRUD, role/permission changes, notification preference edits, and Mailpit ingestion events.
- [ ] Build the audit log UI (filters, detail drawer) so admins can browse the history.

## 2. Navigation & Appearance Enhancements

- [ ] Bring over the curated sidebar grouping from Geraye and store it in a single config (used by the Vue sidebar + policy checks).
- [ ] Add a persistent light/dark theme toggle in the global header (hook into the existing appearance settings so changes sync both ways).
- [ ] Surface “quick actions” (theme toggle, impersonation leave link, profile dropdown) consistently on every page header.

## 3. Mobile/API Readiness

- [ ] Draft `ApiIntegrationGuide.md` that defines versioning, authentication (token + optional OAuth), pagination format, and error envelope.
- [ ] Ship base API scaffolding: auth endpoints (login, refresh, profile), notification feed, and user management summary – all guarded by Sanctum.
- [ ] Generate an OpenAPI spec + Postman collection and store them under `docs/api/`.
- [ ] Add automated API smoke tests (Pest/PHPUnit) for the new endpoints.

## 4. Developer Experience & Tooling

- [ ] Provide artisan generators for “clean architecture” modules (controller + service + DTO + form request + resource).
- [ ] Set up default CI pipeline (lint, tests, build) and document it in the README.
- [ ] Ensure docker-compose/devcontainer configs exist for quick onboarding.

## 5. Optional Enhancements (Prioritize After Core Tasks)

- [ ] Localization scaffold (translation files, helper components, i18n guide).
- [ ] Observability baseline: logging format, exception handler hooks (Sentry/Bugsnag), `/health` endpoint.
- [ ] Feature toggle support using Laravel Pennant (document rollout best practices).

Update this tracker as tasks move to “done” so we always know what’s left before the boilerplate graduates to production-ready status.

# The Second Base – Feature Task List

Use this page to track the next wave of boilerplate upgrades. Each section mirrors the corresponding headers inside `BoilerplateFeaturesTasks.md`, so you can expand details or jot down progress notes while keeping the main tracker tidy.

## 1. Activity Logging & Audit UI

- [x] Publish the activity logging matrix (responsible models/events) in `BoilerplateFeaturesPlan.md`.
- [x] Expand `RecordsActivity` coverage to user/staff CRUD, role/permission changes, notification preference edits, and Mailpit ingestion events.
- [ ] Build the admin-facing audit log UI with filters and entry details.

## 2. Navigation & Appearance Enhancements

- [x] Port the sidebar grouping/config from Geraye and centralize it for both the Vue sidebar and policy checks.
- [x] Expose a global light/dark theme toggle (header quick action) that syncs with appearance settings.
- [x] Standardize header quick-actions (theme toggle, impersonation exit, profile menu) across all pages.

## 3. Mobile/API Readiness

- [x] Create `ApiIntegrationGuide.md` documenting versioning, auth flows, pagination, and error envelopes.
- [x] Implement Sanctum-powered base API (auth, profile, notification feed, user summaries) with clean architecture services.
- [x] Generate an OpenAPI spec and Postman collection; store under `docs/api/`.
- [x] Add automated API smoke tests (Pest/PHPUnit).
- [x] Wire WebSockets or Laravel Reverb for real-time updates.

## 4. Developer Experience & Tooling

- [x] Author artisan generators for module scaffolding (controller + service + DTO + request + resource).
- [x] Ship CI templates (lint/test/build) and document usage in the README.
- [x] Provide docker-compose/devcontainer configs for quick dev setup.

## 5. Optional Enhancements ( I don't want this for now)

- [ ] Localization scaffold (translation files, helper components, i18n guide).
- [ ] Observability baseline (logging format, Sentry/Bugsnag hooks, `/health` endpoint).
- [ ] Feature toggles using Laravel Pennant with documented rollout best practices.

# Asset Management Refactor Plan

This document tracks the work needed to realign the AssetManagement project with the GerayeHealthcare_2 architecture (clean domain layers, admin/staff separation, reusable services). Update the checklist as we complete each stage.

---

## Guiding Principles
- Mirror Geraye’s domain-driven structure so modules drop into admin/staff contexts without duplication.
- Preserve working features during the transition by refactoring incrementally (one module or layer at a time).
- Keep shared UI components/layouts in common folders; isolate admin vs. staff experiences under clear namespaces.
- Add or update documentation alongside the code so the boilerplate stays self-explanatory.

---

## Phase 1 – Repository Preparation
- [ ] Snapshot current state (tag or branch) before structural changes.
- [ ] Enable strict coding standards/linting (ESLint/Prettier, PHP-CS-Fixer) for consistent style (optional but recommended).
- [ ] Review existing service classes/helpers to decide what belongs in shared namespaces vs. domain namespaces.
- [ ] Confirm tests run green (baseline before refactor).

---

## Phase 2 – Backend Architecture
### 2.1 Domain Layering
- [ ] Introduce `app/Domain/{Module}` folders for entities, value objects, policies, data mappers.
- [ ] Move Eloquent models under `app/Domain/{Module}/Models` (or keep in `app/Models` with thin domain proxies) – align with Geraye convention.
- [ ] Extract business logic from controllers into service classes (`app/Application/{Module}`) similar to Geraye’s services.
- [ ] Relocate reusable helpers (notifications, exports, activity logging) to shared domain/application namespaces.

### 2.2 HTTP Layer
- [ ] Create namespaced controllers: `app/Http/Controllers/Admin/{Module}` and `app/Http/Controllers/Staff/{Module}`.
- [ ] Update routes to map to new namespaces (`routes/admin.php`, `routes/staff.php`, etc.) or group within `routes/web.php`.
- [ ] Adjust policies/middleware to reflect admin vs staff access.
- [ ] Ensure request/response DTOs or resources align with the new structure (e.g., move resources to `app/Http/Resources/{Module}`).

### 2.3 Jobs & Notifications
- [ ] Relocate alerts/notification logic to domain-specific jobs under `app/Domain/{Module}/Jobs` (mirroring Geraye).
- [ ] Centralize notification classes under `app/Domain/Shared/Notifications` or per-module namespaces.
- [ ] Keep queue configuration (Horizon) ready for Phase 6 alerts once refactor is stable.

---

## Phase 3 – Frontend Structure
### 3.1 Layouts & Components
- [ ] Split layout files into admin vs staff versions (e.g., `resources/js/layouts/admin`, `resources/js/layouts/staff`).
- [ ] Move shared components to `resources/js/components/shared` while module-specific UI lives under module folders.

### 3.2 Page Organization
- [ ] Relocate admin-facing pages to `resources/js/pages/Admin/{Module}` (Staff management, Users, Roles, Dashboard, Exports).
- [ ] Prepare `resources/js/pages/Staff/{Module}` for staff-specific flows (even if placeholders for now).
- [ ] Update Inertia route imports (`resources/js/routes`) to point at new page locations.
- [ ] Review shared composables/stores; move to `resources/js/composables` or `resources/js/store` and ensure they’re admin/staff agnostic.

### 3.3 Styling & Assets
- [ ] Consolidate theme tokens (tailwind config, CSS) to support both admin and staff sections.
- [ ] Ensure new folder structure works with Vite aliases (`resources/js/…` needs updated aliases if paths change significantly).

---

## Phase 4 – Module Migration Strategy
- [ ] Refactor modules one by one following this order (admin focus first): Staff → Users → Roles → Exports → Dashboard → Notifications.
- [ ] For each module: move backend files, move frontend pages/components, adjust routes, migrate tests.
- [ ] Run tests + manual smoke check after each module migration before proceeding to the next.

---

## Phase 5 – Documentation & Tests
- [ ] Update `README.md` or create `docs/architecture.md` describing the new folder layout and module conventions.
- [ ] Expand existing guides (`FeatureImplementationGuide.md`, pagination, email, messaging) to reference new paths.
- [ ] Ensure Pest/PHPUnit tests point to new namespaces; add missing unit/feature tests where gaps exist.
- [ ] Update front-end component stories or Storybook (if used) to reflect new locations.

---

## Verification Checklist
- [ ] All routes respond with correct layouts after refactor.
- [ ] Tests (`php artisan test`, front-end lint/build) pass without warnings.
- [ ] IDE/autoloading recognizes new namespaces (`composer dump-autoload`).
- [ ] Frontend build (`npm run build` / `vite`) succeeds with updated imports.
- [ ] Documentation reflects the new architecture and onboarding steps.

---

## Notes
- All changes should remain backward-compatible until the final module migrates to avoid blocking ongoing work.
- Keep commits scoped (per module or layer) to aid review and rollback if needed.
- If a pattern from Geraye proves overly specific to healthcare, adapt terminology to asset management while preserving reusable structure.

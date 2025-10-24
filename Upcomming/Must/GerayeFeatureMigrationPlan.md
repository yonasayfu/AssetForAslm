# Geraye Feature Migration Plan

## Objectives
- Establish a clean Asset Management codebase that inherits polished UX, RBAC, and productivity tooling from `gerayehealthcare_2` while keeping domain logic asset-centric.
- Preserve the "liquid glass" styling, reusable CRUD scaffolds, export/print system, and notification patterns so the new project feels feature-complete on day one.
- Avoid dragging across healthcare-specific models, migrations, copy, or workflows; only re-platform reusable infrastructure, UI, and collaboration layers.

## Out-of-Scope
- Patient, medical, insurance, or visit/service modules from Geraye.
- Flutter/mobile code unless explicitly re-used for future parity (document only).
- Legacy status docs or historical sprint artifacts that do not inform reusable features.

## Feature Migration Targets

| Category | Source in Geraye | Adaptation for Asset Management |
| --- | --- | --- |
| Application foundation | `ARCHITECTURE.md`, `Development_Guides.md`, base Laravel service/providers | Recreate service-layer + repository pattern scaffolding; keep modular folder layout and request/response DTO approach. |
| Auth & RBAC | `RBAC_AND_UI_CONSISTENCY_GUIDE.md`, `RBAC_ROLE_ACCESS_MATRIX.md`, policies | Port Spatie Permission setup, middleware stack, policy patterns; redefine roles (`Admin`, `Manager`, `Technician`, etc.) per `Upcomming/README.md`. |
| Navigation & Layout | Dual layout integration described in user brief; `UI_and_Templates.md`; sidebar conventions | Migrate header/sidebar layouts, navigation guards, and glass-themed components; align menu entries with `MyNewAppsidebar.md`. |
| Styling system | `resources/css/app.css` (liquid glass classes), `UI_and_Templates.md` | Copy liquid-glass utility classes, button styles, card templates; ensure Tailwind config matches colors/gradients used in CRUD and dashboard screens. |
| List, search & filters | `SEARCH.md`, table components (`useTableFilters`, Pagination.vue) | Reuse table composables, URL filter syncing, reusable filter components; adapt to Asset CRUD (Assets, Maintenance, Warranty, etc.). |
| Export & print | `PrintableReport.vue`, `ExportableTrait`, `PRINT_*` guides | Implement the same export trait + queue strategy; reuse printable layouts for asset reports, maintenance logs, audit sheets. |
| Notification layer | Toast notifications, notification bell, real-time alerts described in `GERAYE-ORGANIZED.md` | Bring flash/toast components, notification dropdown, alert scheduling pattern; wire into asset alerts (maintenance due, lease expiring). |
| Messaging & collaboration | `MESSAGING_SYSTEM_DOCUMENTATION.md`, task delegation docs | Evaluate need for in-app messaging/tasks; port modular messaging store/services and task assignment once core modules are in place. |
| Activity tracking | audit logs referenced in architecture docs | Reapply activity log traits for CRUD history (asset movements, maintenance updates). |
| File handling | `STORAGE.md`, document upload flows | Reuse folder structure, upload components, document preview modals for asset documents/images. |
| Global search | GlobalSearch modal pattern | Integrate global search overlay with asset-aware sources (assets, people, maintenance tickets). |
| Dashboard widgets | KPI cards, chart components, alert calendar from dashboard module | Repurpose widget system for asset KPIs (total assets, value, expiring warranties) and align with roadmap day-13 deliverables. |
| API scaffolding | `MOBILE_API.md`, service serialization patterns | Use API resource patterns to keep future mobile integration easy; expose REST endpoints for assets and operations. |

## Migration & Build Phases

### Phase 0 – Discovery & Extraction
- Catalog reusable Vue components, Laravel traits, services, and configuration in Geraye.
- Flag dependencies that rely on healthcare-specific tables to avoid copying unintended logic.

### Phase 1 – Core Foundation
- Bootstrap Laravel/Inertia baseline (already in AssetManagement) with Geraye service provider conventions.
- Integrate Spatie Permission, seed boilerplate roles, and port policy gates/middleware.
- Add shared response macros, custom exceptions, and helper traits.

### Phase 2 – Application Shell & UX Consistency
- Bring over layout files (AppLayout, GuestLayout), navigation guards, breadcrumb helpers.
- Apply liquid glass design tokens and CSS utilities; confirm dark/light compatibility.
- Implement reusable components (GlassCard, GlassButton, DataTable, FilterBar, Modal, Toast).

### Phase 3 – Cross-Cutting Features
- Re-implement global search modal, table filter composables, pagination UX.
- Install export/print pipeline (queues, `ExportableTrait`, unified print templates).
- Restore notification center, toast stack, and scheduled alert jobs.
- Re-enable activity logging and changelog timelines.

### Phase 4 – Collaboration Layer (Optional in MVP)
- Decide scope for in-app messaging; if included, adapt message models with asset-focused channels (maintenance teams, auditors).
- Port task delegation/to-do features for maintenance assignments and audit follow-ups.

### Phase 5 – Domain Module Implementation
- Use roadmap in `MyNewProjectRoadmap.md` to stand up taxonomy, assets, maintenance, warranties, audits, and reporting modules leveraging migrated scaffolds.
- Ensure each module plugs into search, export/print, notifications, activity logs, and dashboards.

### Phase 6 – API & Mobile Readiness
- Expose REST endpoints mirroring web features using Geraye's API patterns.
- Document mobile parity requirements for future Flutter work; no code migration yet.

## Task Backlog

### Foundation & Security
- [ ] Compare Geraye `composer.json` / `package.json` to ensure required packages (Spatie Permission, Laravel Excel, Inertia helpers) are installed.
- [ ] Port configuration for roles, permissions, and policies; redefine role matrix for Asset Management.
- [ ] Configure route middleware stacks (`auth`, `permission`, `verified`) consistent with Geraye patterns.

### Layout & Styling
- [ ] Copy base layouts, navigation components, and breadcrumb helpers; reconcile with `MyNewAppsidebar.md`.
- [ ] Bring liquid glass CSS utilities and ensure Tailwind config includes needed theme extensions.
- [ ] Migrate shared Vue components (cards, buttons, modal, tabs) used across CRUD and dashboard pages.

### CRUD Experience
- [ ] Transfer DataTable + filter composables, sorting, pagination components.
- [ ] Implement reusable form components (input groups, select with search, date pickers) to match Geraye UX.
- [ ] Integrate toast notification helpers and confirmation modal patterns into asset CRUD flows.

### Exporting, Printing, & Reporting
- [ ] Port `ExportableTrait`, queued export jobs, and Blade/Vue print templates.
- [ ] Align asset, maintenance, audit reports with unified print layout; map to roadmap reporting modules.
- [ ] Recreate centralized export history pages and storage retention policies.

### Notifications & Collaboration
- [ ] Reuse notification bell dropdown, web push/email notification setup, and scheduling jobs.
- [ ] Adapt alert rules for asset lifecycle events (due/overdue maintenance, expiring warranty, overdue checkout).
- [ ] Evaluate messaging/task modules; if included, rename models/routes and strip healthcare vocabulary.

### Activity & Audit Trails
- [ ] Port activity log traits/events; attach to asset movements, maintenance updates, reservation approvals.
- [ ] Ensure change history surfaces inside Detail views with consistent timeline components.

### API & Integration Prep
- [ ] Mirror Geraye's API resource/transformer approach for asset endpoints.
- [ ] Document authentication/token strategy for future mobile clients (Sanctum).
- [ ] Set up Postman/Insomnia collections derived from Geraye's mobile API docs.

### Documentation & QA
- [ ] Update `Upcomming/ROADMAP.md` and `TASKS.md` with migration status checkpoints.
- [ ] Create developer onboarding notes referencing migrated components and their new locations.
- [ ] Plan regression tests (PHPUnit + Vue tests) for shared components as they are reintroduced.

## Dependencies & Open Questions
- Confirm which collaboration features (messaging, task assignment) are required in MVP versus later phases.
- Verify licensing/attribution requirements for any third-party assets copied from Geraye.
- Decide whether to maintain PostgreSQL (Geraye) or switch to MySQL as default; adjust migrations accordingly.
- Determine how much of the dashboard widget engine should be generic versus tailored to Asset KPIs up front.

## References
- `gerayehealthcare_2/MD/GERAYE-ORGANIZED.md`
- `gerayehealthcare_2/MD/GROUP_5_DEVELOPER_GUIDES`
- `AssetManagement/Upcomming/MyNewProjectRoadmap.md`
- `AssetManagement/Upcomming/MyNewAppsidebar.md`


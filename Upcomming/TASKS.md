# Task Backlog

Status keys: [P0]=critical, [P1]=high, [P2]=normal

## Milestone 1 — Foundations
- [P0] Seed roles/permissions and basic policies
  - AC: Default roles present; policy checks restrict access on a sample route.
- [P0] App shell + sidebar IA
  - AC: Layout with collapsible groups per `MyNewAppsidebar.md`.
- [P1] Taxonomy models & CRUD (Sites, Locations, Departments, Categories, Conditions)
  - AC: CRUD pages, soft deletes, unique constraints, basic filters.
- [P1] CI bootstrap scripts (install/lint/test)
  - AC: One command sets up dev; linters pass on clean checkout.

## Milestone 2 — Assets & Lifecycle
- [P0] Assets schema + CRUD + policies
  - AC: Create/edit/view with photo, taxonomy links, custom fields (JSON or separate table).
- [P0] Lifecycle services (checkout/checkin, lease/return, reserve, move, dispose)
  - AC: State transitions validated; activity logged; permissions enforced.
- [P1] Documents & Images attachments
  - AC: Upload/list/delete; storage config local/S3.
- [P1] Import (template + validation) and Export (CSV/XLSX)
  - AC: Background jobs; progress and error reporting.

## Milestone 3 — Maintenance & Warranties & Alerts
- [P0] Maintenance module (statuses, assign, scheduling)
- [P0] Warranty module with active/expiry logic
- [P1] AlertService + scheduler (hourly/daily)
  - AC: Emails sent; dashboard shows due/expiring windows.

## Milestone 4 — Audits & Reporting & Import/Export
- [P0] Audit flows (start, scan, review, close) with audit lines
- [P1] Saved reports (definition JSON) + export
- [P1] Horizon setup; failure notifications

## Cross-Cutting
- [P0] Access policy tests for each domain action
- [P1] Error handling UX (toasts, validation summaries)
- [P2] Performance pass (indexes, N+1 checks, pagination)

## Icebox
- PWA/mobile endpoints (Sanctum)
- Advanced search (Scout + engine)


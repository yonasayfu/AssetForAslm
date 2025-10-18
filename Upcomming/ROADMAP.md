# Roadmap

This roadmap organizes delivery into focused milestones. For detailed module specs, see `Upcomming/MyNewProjectRoadmap.md`. For schema, see `Upcomming/MyNewDatabaseSchema.md`.

## Milestone 1 — Foundations
- Auth, RBAC (roles/permissions) seeded
- App shell (layout, sidebar IA, glass UI)
- Core taxonomy: Sites, Locations, Departments, Categories, Conditions
- CI wiring (install, lint, basic tests)

Deliverables:
- Login/register/forgot-password flows
- Role-based navigation and policies enforced on a sample page

## Milestone 2 — Assets & Lifecycle
- Assets CRUD with status machine (available, checked_out, leased, reserved, under_repair, terminal)
- Documents & Images attachments
- Lifecycle services: checkout/checkin, lease/return, reserve, move, dispose

Deliverables:
- List/detail pages, import template, export to CSV/XLSX
- Activity log entries for operations

## Milestone 3 — Maintenance & Warranties & Alerts
- Maintenance CRUD, statuses, assignees
- Warranty model with active derivation and expiry
- AlertService + scheduler for due/expiring items

Deliverables:
- Dashboard KPIs + charts + calendar of upcoming items
- Email notifications for alerts

## Milestone 4 — Audits & Reporting & Import/Export
- Audit flows (start, scan, review, close) with audit lines
- Saved reports (definition JSON) and export
- Import wizard with queued jobs and validation

Deliverables:
- Report families (assets, maintenance, leases, reservations, status)
- Background job monitoring (Horizon) and error handling

## Later
- Customers & commerce polish (purchases, invoices)
- Advanced search (Scout/Meilisearch/Algolia)
- PWA/mobile API endpoints

## Links
- Features: `Upcomming/MyNewProjectRoadmap.md`
- Schema: `Upcomming/MyNewDatabaseSchema.md`
- Sidebar: `Upcomming/MyNewAppsidebar.md`
- Backlog: `Upcomming/TASKS.md`


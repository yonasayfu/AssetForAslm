# Project Owner Notes

## Goals
- Reuse AssetTiger boilerplate to accelerate delivery.
- Ship a clean, standalone codebase (no healthcare-specific residue).
- Prioritize asset lifecycle, alerts, and reporting as v1 pillars.

## Non-Goals (v1)
- Multi-tenant architecture (single-tenant only).
- Complex procurement/finance beyond basic purchases.
- Heavy CMS features.

## Carry-Over From Previous Project
- Auth + RBAC (Spatie) and policies
- Global search (Scout-ready)
- Notifications (mail), toasts, exports/imports (Maatwebsite/Excel)
- Clean architecture: services, DTOs, policies
- UI shell: sidebar group/collapse, glass effect style

## Naming & Conventions
- Use neutral, domain-consistent names: Asset, Maintenance, Warranty, Audit, Site, Location, Department, Person.
- Money in minor units; store currency per row.
- Timestamps: `*_at`; soft deletes where reasonable.

## Acceptance Criteria (v1)
- Create/view/update assets with taxonomy, status, documents/images.
- Perform check-out/in, reserve, lease/return with audit trail.
- Track maintenance and warranties with due/expiring alerts.
- Basic dashboard KPIs and core reports exportable to CSV/XLSX.

## Risks & Mitigations
- Import scale: process in chunks via queues; throttle.
- Permissions gaps: enforce via policies; integration tests for critical flows.
- Schema drift: lock with migrations; document in `MyNewDatabaseSchema.md`.

## Release Strategy
- Milestone 1: Foundations (Auth/RBAC/UI shell/Taxonomy)
- Milestone 2: Assets + Lifecycle
- Milestone 3: Maintenance + Warranties + Alerts
- Milestone 4: Audits + Reporting + Import/Export

## References
- Feature scope: `Upcomming/MyNewProjectRoadmap.md`
- Schema draft: `Upcomming/MyNewDatabaseSchema.md`
- Sidebar IA: `Upcomming/MyNewAppsidebar.md`
- Tasks backlog: `Upcomming/TASKS.md`


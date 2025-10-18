# AssetTiger Boilerplate — Upcoming Project

- Project owner guide: `Upcomming/PROJECT-OWNER.md`
- AI agents guide: `Upcomming/AGENTS.md`
- Roadmap: `Upcomming/ROADMAP.md`
- Task backlog: `Upcomming/TASKS.md`
- Sidebar concept: `Upcomming/MyNewAppsidebar.md`
- Database schema draft: `Upcomming/MyNewDatabaseSchema.md`
- Detailed functional scope: `Upcomming/MyNewProjectRoadmap.md`

## Overview

This is the planning and documentation area for the next application built from the AssetTiger Laravel/Inertia/Vue boilerplate. It carries forward core platform features: authentication, RBAC, global search, notifications, exports/printing, clean architecture, and responsive UI components.

## For End Users

High-level promise:
- Manage assets and lifecycle (check-out/in, lease, reserve, move, dispose).
- Track maintenance, warranties, audits, and alerts.
- Import/export data and generate reports.

See the feature inventory in `Upcomming/MyNewProjectRoadmap.md`.

## Getting Started (Developer Setup)

Requirements:
- PHP 8.2+, Composer
- Node 18+, npm or pnpm
- MySQL or PostgreSQL

Install:
1) Copy `.env.example` to `.env` and set DB credentials.
2) `composer install`
3) `php artisan key:generate`
4) `php artisan migrate --seed` (seeds roles and an admin user if configured)
5) `npm install` and `npm run dev`
6) `php artisan serve` then open the printed URL.

Default roles: Admin, Manager, Technician, Staff, Auditor, Read-only.

## Configuration

- Queues: enable Redis + Horizon for background jobs (imports, alerts, reports).
- Files: configure local or S3 storage for documents/images.
- Mail: set `MAIL_` vars for notifications.

## Quality & Support

- Testing: `phpunit` (PHP) and `vitest`/`jest` (JS) where applicable.
- Linting: `composer lint` (if defined) and `npm run lint`.
- Issue tracking: use tasks in `Upcomming/TASKS.md` and milestones in `Upcomming/ROADMAP.md`.

## Notes

- All work for the upcoming project should be planned here before code changes.
- Avoid project-specific names from older apps; keep boilerplate generic.


# Asset Management Boilerplate – Planning Index

This folder collects the planning notes we want to keep long term. Start here when you need context or a deep-dive reference.

## Quick Index
- Vision & ownership: PROJECT-OWNER.md
- Agent workflow guidance: Agent.md
- Roadmap & backlog: ROADMAP.md, TASKS.md
- Feature migration plans: GerayeFeatureMigrationPlan.md, GerayeFeatureMigrationTasks.md
- Detailed scope: MyNewProjectRoadmap.md
- Schema blueprint: MyNewDatabaseSchema.md
- Implementation guides: see FeatureImplementationGuide.md, BoilerplateFeaturesPlan.md, and the various *Guide.md files for email, Mailpit, MFA, notifications, pagination, printing, etc.

## Overview

This is the planning and documentation area for the next application built from the AssetTiger Laravel/Inertia/Vue boilerplate. It carries forward core platform features: authentication, RBAC, global search, notifications, exports/printing, clean architecture, and responsive UI components.

## For End Users

High-level promise:
- Manage assets and lifecycle (check-out/in, lease, reserve, move, dispose).
- Track maintenance, warranties, audits, and alerts.
- Import/export data and generate reports.

See the feature inventory in MyNewProjectRoadmap.md.

## Getting Started (Developer Setup)

Requirements:
- PHP 8.2+, Composer
- Node 18+, npm or pnpm
- MySQL or PostgreSQL

Install:
1. Copy .env.example to .env and set DB credentials.
2. composer install
3. php artisan key:generate
4. php artisan migrate --seed (seeds roles and the sample admin user)
5. 
pm install and 
pm run dev
6. php artisan serve then open the printed URL.

Default roles: Admin, Manager, Technician, Staff, Auditor, Read-only.

## Configuration

- Queues: enable Redis + Horizon for background jobs (imports, alerts, reports).
- Files: configure local or S3 storage for documents/images.
- Mail: set MAIL_ vars for notifications (see the Mailpit/Email guides for details).

## Quality & Support

- Testing: phpunit (PHP) and itest/jest (JS) where applicable.
- Linting: composer lint (if defined) and 
pm run lint.
- Issue tracking: keep TASKS.md and ROADMAP.md in sync with current priorities.

## Notes

- Stage new ideas here before touching application code.
- Keep terminology boilerplate-friendly—avoid project-specific names.

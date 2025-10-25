# Asset Management Boilerplate – Complete User Guide

## 🚀 Platform Overview

The Asset Management boilerplate ships with a production-ready RBAC model, Sanctum-secured APIs, and Inertia-powered UI. This guide explains how to operate and test the system from the perspective of each seeded role (**Admin**, **Manager**, **Technician**, **ReadOnly/Auditor**, **External/Guest**), and how to use the new sample placeholder pages to validate permissions quickly.

---

## 🛠️ Getting Started

| Step | Command / Action | Purpose |
| --- | --- | --- |
| 1 | `cp .env.example .env` | Configure environment |
| 2 | `composer install && npm install` | Install dependencies |
| 3 | `php artisan key:generate` | Set app key |
| 4 | `php artisan migrate --seed` | Create schema + seed roles/users |
| 5 | `npm run dev` | Build frontend assets |
| 6 | `php artisan serve` | Start the HTTP server |

> **Tip:** The seeder inserts notifications and sample staff records so the dashboard feels alive on first login.

### Default Test Accounts

| Role | Email | Password | Notes |
| --- | --- | --- | --- |
| Admin | `admin@example.com` | `password` | Full control (users, roles, staff, impersonation) |
| Manager | `manager@example.com` | `password` | Staff CRUD only |
| Technician | `technician@example.com` | `password` | Staff read-only |
| Staff | `staff@example.com` | `password` | Mirrors Technician permissions |
| Auditor | `auditor@example.com` | `password` | Read-only auditing |
| ReadOnly | `readonly@example.com` | `password` | Intended as the base for guest/external testing |

> To test the **External** (guest) experience, log in as `readonly@example.com`, impersonate as Admin, and change the role to **External** inside the Users module.

---

## 🔐 Role Access Matrix

| Feature | Admin | Manager | Technician/Staff/Auditor | External (Guest) |
| --- | --- | --- | --- | --- |
| Dashboard & Notifications | ✅ | ✅ | ✅ | ✅ |
| Staff Index / Export | ✅ | ✅ | ✅ (view only) | 🚫 |
| Staff Create / Edit / Delete | ✅ | ✅ | 🚫 | 🚫 |
| Users Module | ✅ | 🚫 | 🚫 | 🚫 |
| Roles Module | ✅ | 🚫 | 🚫 | 🚫 |
| Activity Logs | ✅ | 🚫 | 🚫 | 🚫 |
| Impersonation | ✅ (via `users.impersonate`) | 🚫 | 🚫 | 🚫 |
| API Tokens (via Sanctum) | ✅ | ✅ (read endpoints) | ✅ (limited) | 🚫 |
| Sample Placeholder Page | `/samples/admin` | `/samples/manager` | `/samples/technician` | `/samples/external` |

Legend: ✅ = permitted, 🚫 = denied (expect 403 or hidden navigation).

---

## 🧭 Sample Placeholder Pages

To make RBAC validation painless, the boilerplate now includes lightweight Inertia pages under `/samples`. Each route is protected by the expected role middleware.

| Route | Audience | Middleware | Purpose |
| --- | --- | --- | --- |
| `/samples` | Any authenticated user | `auth` | Landing page linking to all samples |
| `/samples/admin` | Admin | `role:Admin` | Confirms full platform control |
| `/samples/manager` | Manager | `role:Manager` | Verifies staff management access only |
| `/samples/technician` | Technician | `role:Technician` | Confirms read-only operations |
| `/samples/external` | External / ReadOnly | `role:External|ReadOnly` | Confirms dashboard-only experience |

> If you hit a 403 on a page you should own, check the user’s role assignment in **Users → Edit User**.

---

## 👤 Role-Specific Guides

### 1. Admin
- Sign in with `admin@example.com`.
- Navigate to **Staff**, **Users**, **Roles** — all should load.
- Visit `/samples/admin` and walk through the checklist.
- Optional: use the user menu → **Impersonate** to test other roles.
- Run automated API checks: `./vendor/bin/pest tests/Feature/Api`.

### 2. Manager
- Sign in with `manager@example.com`.
- Confirm **Staff** shows create/edit buttons.
- Verify `/users` and `/roles` return 403.
- Visit `/samples/manager` and complete the suggested flow.

### 3. Technician (applies to Staff, Auditor as well)
- Sign in with `technician@example.com`.
- Staff list loads, but edit/delete buttons are hidden.
- `/users` and `/roles` should respond with 403.
- Visit `/samples/technician` to confirm expectations.

### 4. External / Guest
- Log in with `readonly@example.com` (after converting to **External** role).
- Sidebar should only show Dashboard + Settings.
- `/staff`, `/users`, `/roles` should redirect or 403.
- Visit `/samples/external` to confirm the restricted view.

---

## ✅ RBAC Validation Checklist

1. **Smoke the sample routes**
   - `php artisan serve`
   - Visit `/samples/admin`, `/samples/manager`, `/samples/technician`, `/samples/external` using the corresponding accounts.
2. **Navigation audit**
   - Compare the sidebar groupings per role against the matrix above.
3. **Permission denial audit**
   - Attempt restricted pages (Users/Roles) with Manager and Technician — confirm 403.
4. **API surface**
   - Authenticate via `/api/v1/auth/login`, then call `/api/v1/users` with Admin token (works) and Technician token (should return 403).
5. **Automated regression**
   - `./vendor/bin/pest tests/Feature/Api`
   - `./vendor/bin/pest tests/Feature/UserShowTest.php`

Document the results in your QA notes. If any unexpected access is observed, re-run `php artisan permission:cache-reset` (or `php artisan optimize:clear`) after adjusting roles.

---

## 🧪 Creating Additional Samples

1. Add a Vue page under `resources/js/pages/samples`.
2. Register a route inside `routes/web.php` within the existing `Route::prefix('samples')` group.
3. Protect it with the desired middleware, e.g. `->middleware('permission:staff.update')`.
4. Update the table in this guide to keep testers aligned.

---

## 🛟 Troubleshooting

| Issue | Likely Cause | Fix |
| --- | --- | --- |
| Sample page immediately redirects | Missing role assignment | Update the user via **Users → Edit → Roles** |
| Sidebar still shows Users/Roles for Technician | Cached permissions | `php artisan permission:cache-reset` |
| 419 CSRF on API tests | Token missing or expired | Re-authenticate via `/api/v1/auth/login` |
| 404 on `/samples/...` | Routes not cached / environment mismatch | `php artisan route:clear` |
| Seeder users absent | Migrate skipped seeding | `php artisan migrate:fresh --seed` |

---

## 📚 References

- `database/seeders/RolePermissionSeeder.php` – role → permission mapping
- `database/seeders/DatabaseSeeder.php` – seeded users & staff
- `routes/web.php` → `/samples` group – sample placeholders
- `resources/js/pages/samples` – Inertia sample pages
- `MD/Upcomming/Must/ApiTest.md` – API smoke testing playbook

Keep this document alongside QA scripts so new teammates can validate RBAC within minutes. Happy testing! 🎯

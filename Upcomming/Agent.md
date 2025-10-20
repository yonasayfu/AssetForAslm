# Agent Handoff – AssetManagement Boilerplate

## Status Snapshot (Oct 2025)
- **Phase 0 – Preparation & Environment:** Completed. Core packages (Spatie Permission, Sanctum, Excel/Dompdf, Reverb, Ziggy, Spatie Data) installed; `.env.example` updated with broadcasting keys. Conventions captured in `Upcomming/Phase0-FoundationNotes.md`.
- **Phase 1 – Application Shell & Styling:** Completed. Liquid-glass styling lives in `resources/css/app.css:175`. Shared components (`GlassCard.vue`, `GlassButton.vue`, `Toast.vue`, `ConfirmModal.vue`) and auth layouts (`resources/js/layouts/auth/*`) mirror Geraye. Handoff notes: `Upcomming/Phase1-UIFoundationNotes.md`.
- **Phase 2 – Identity & RBAC:** Staff module mirrors Geraye UI (metrics, filters, styled table). Users module adds CRUD, role/permission assignment, and staff linking (`app/Http/Controllers/UserManagementController.php`, `resources/js/pages/Users/*`). Inertia now shares role-derived permissions/capabilities via `HandleInertiaRequests.php`.
- Task tracker: `Upcomming/GerayeFeatureMigrationTasks.md`. Keep it updated as the single source of truth.

## Code Landmarks
- **UI utilities:** `resources/css/app.css` defines `app-sidebar`, `.btn-glass`, `.liquidGlass-*`.
- **Layouts:** shell `resources/js/layouts/app/AppSidebarLayout.vue`, header `AppSidebarHeader.vue`, auth variants `resources/js/layouts/auth/*`.
- **Shared feedback:** `Toast.vue` consumes flash data; `ConfirmModal.vue` sits in `AppLayout.vue` (use `useConfirm()`).
- **Permissions share:** `HandleInertiaRequests.php` exposes `auth.roles`, `auth.permissions`, and `auth.can` (`viewStaff`, `manageUsers`) so the sidebar can mirror RBAC.

## Current Focus (start here)
1. **Phase 2 – Audit Logging (still open)**
   - Add activity logging for staff/user changes (table or Spatie Activitylog).
   - Surface timelines in detail views; prep API for dashboards.
2. Keep `Upcomming/GerayeFeatureMigrationTasks.md` in sync—Phase 2 checkboxes now reflect Staff + Users completion; auditing is the remaining item.
3. Document manual test guidance for each deliverable; user expects a “test checklist” after every phase.

## How to Continue the Session
- Review `Upcomming/Phase2-IdentityNotes.md` for completed work and what’s left.
- Follow conventions from `Phase0-FoundationNotes.md` (service/DTO structure still planned).
- Commit after logical batches. No remote yet—coordinate with the user before pushing externally.

## Testing & Tooling
- Frontend: `npm run lint`, `npm run build`.
- Backend: `php artisan test`.
- Manual checks now: staff filters/chips, user role assignment UI, navigation visibility (`staff.view`, `users.manage`), two-factor flows, toasts/confirm dialogs.
- Seed helpers: `php artisan db:seed --class=RolePermissionSeeder` and `php artisan permission:cache-reset` keep roles in sync; default admin creds `admin@example.com / password`.

## Notes / Watch-outs
- Keep naming domain-generic (Asset vs. Healthcare).
- Copy only reusable infrastructure from Geraye; avoid domain-specific tables/services.
- Update `.env` (Sanctum, Reverb) when spinning up new environments.
- Ping the user if you hit unexpected diffs—they want to stay in the loop.

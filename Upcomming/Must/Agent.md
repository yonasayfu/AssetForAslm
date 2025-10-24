# Agent Handoff – AssetManagement Boilerplate

## Status Snapshot (Oct 2025)
- **Phase 0 - Preparation & Environment:** Completed. Core packages (Spatie Permission, Sanctum, Excel/Dompdf, Reverb, Ziggy, Spatie Data) installed; `.env.example` updated with broadcasting keys. Conventions captured in `Upcomming/Phase0-FoundationNotes.md`.
- **Phase 1 - Application Shell & Styling:** Completed. Liquid-glass styling lives in `resources/css/app.css:175`. Shared components (`GlassCard.vue`, `GlassButton.vue`, `Toast.vue`, `ConfirmModal.vue`) and auth layouts (`resources/js/layouts/auth/*`) mirror Geraye. Handoff notes: `Upcomming/Phase1-UIFoundationNotes.md`.
- **Phase 2 - Identity & RBAC:** Staff module mirrors Geraye UI (metrics, filters, styled table). Users module adds CRUD, role/permission assignment, and staff linking (`app/Http/Controllers/UserManagementController.php`, `resources/js/pages/Users/*`). Inertia now shares role-derived permissions/capabilities via `HandleInertiaRequests.php`.
- **Phase 4 - Export, Print & File Handling:** Completed. Central export trait + download center (`app/Support/Exports/*`, `resources/js/pages/Exports/Index.vue`) live; print layout at `resources/views/pdf-layout.blade.php`; staff avatars use `FileUploadField.vue`. Manual checklist recorded in `Upcomming/Phase4-ExportsNotes.md`.
- Task tracker: `Upcomming/GerayeFeatureMigrationTasks.md`. Keep it updated as the single source of truth.

## Code Landmarks
- **UI utilities:** `resources/css/app.css` defines `app-sidebar`, `.btn-glass`, `.liquidGlass-*`.
- **Layouts:** shell `resources/js/layouts/app/AppSidebarLayout.vue`, header `AppSidebarHeader.vue`, auth variants `resources/js/layouts/auth/*`.
- **Shared feedback:** `Toast.vue` consumes flash data; `ConfirmModal.vue` sits in `AppLayout.vue` (use `useConfirm()`).
- **Permissions share:** `HandleInertiaRequests.php` exposes `auth.roles`, `auth.permissions`, and `auth.can` (`viewStaff`, `manageUsers`) so the sidebar can mirror RBAC.

## Current Focus (start here)
1. **Phase 2 - Audit Logging (still open)**
   - Add activity logging for staff/user changes (table or Spatie Activitylog).
   - Surface timelines in detail views; prep API for dashboards.
2. **Phase 5 - Notifications Prep**
   - Confirm MVP scope for notification center, alert badges, and delivery channels before implementation.
3. **Documentation cadence**
   - Keep `Upcomming/GerayeFeatureMigrationTasks.md` and `Upcomming/Phase4-ExportsNotes.md` current with manual test checklists and any follow-up work.
- Frontend: `npm run lint`, `npm run build`.
- Backend: `php artisan test`.
- Manual checks now: staff filters/chips, user role assignment UI, navigation visibility (`staff.view`, `users.manage`), two-factor flows, toasts/confirm dialogs.
- Seed helpers: `php artisan db:seed --class=RolePermissionSeeder` and `php artisan permission:cache-reset` keep roles in sync; default admin creds `admin@example.com / password`.

## Notes / Watch-outs
- Keep naming domain-generic (Asset vs. Healthcare).
- Copy only reusable infrastructure from Geraye; avoid domain-specific tables/services.
- Update `.env` (Sanctum, Reverb) when spinning up new environments.
- Ping the user if you hit unexpected diffs—they want to stay in the loop.




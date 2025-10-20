# Phase 2 Kick-off – Identity, Staff & RBAC

## Goals
- Establish a generic **Staff** domain module with CRUD, policies, and Inertia screens.
- Enable **Spatie Permission** for role/permission management across web + API.
- Bring user account flows (create/edit, password reset, profile) up to Geraye standards while keeping naming asset-agnostic.
- Add minimal **audit logging** around user/staff activity to feed later reporting.

## Scope Breakdown
1. **Domain & Database**
   - `staff` table with basic fields (name, contact, status, role hints, linked `user_id`).
   - Factories + seeders to create sample staff and role assignments.
   - Optional supporting tables (departments, etc.) can defer to Phase 7 taxonomy.
2. **Models & Relationships**
   - `App\Models\Staff` with fillable attributes, `belongsTo` user, `appends` for full name/photo URL later.
   - Update `App\Models\User` to include `HasRoles`, `HasApiTokens`, `staff()` relationship, and profile helpers.
3. **Policies & Permissions**
   - Publish Spatie config + migration (`php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`).
   - Seed base roles from checklist (`Admin`, `Manager`, `Technician`, `Staff`, `Auditor`, `ReadOnly`) with example permissions (e.g., `staff.view`, `staff.create`, etc.).
   - Register policies in `AuthServiceProvider`, gate routes/controllers, and surface guard logic to Vue (`PermissionGuard` from Geraye).
4. **HTTP Layer**
   - Resource controller or Inertia actions for Staff index/create/edit/show.
   - Form requests, service/action classes mirroring Geraye architecture (thin controllers).
   - Include confirm/toast usage introduced in Phase 1.
5. **Frontend**
   - Pages under `resources/js/pages/Staff/*` (Index, Create, Edit, Show).
   - Reuse new glass components for headers/cards; connect to toasts & confirmation dialog.
   - Permission-based navigation visibility (update `NavMain` to pull routes from role matrix once defined).
6. **Audit & Activity**
   - Add activity logging (e.g., `ActivityLog` model or reuse Laravel events) for staff create/update/delete.
   - Prepare traits/helpers for future modules (maybe `RecordsActivity` trait).

## References (Geraye)
- `app/Models/Staff.php`, `app/Http/Controllers/Admin/StaffController.php`, `resources/js/pages/Admin/Staff/*` for structure inspiration.
- RBAC docs: `MD/GROUP_2_SECURITY_RBAC/RBAC_AND_UI_CONSISTENCY_GUIDE.md`, `RBAC_ROLE_ACCESS_MATRIX.md`.
- Permission guard: `resources/js/components/PermissionGuard.vue` + composables.

## Deliverables for this Phase
- Migrations, models, policies, controllers, routes, Vue pages for Staff + role seeding.
- Updated documentation: `Phase2` notes summarizing schema + guard usage.
- Testing notes: manual checklist for staff CRUD, role enforcement, login flows.

## Open Questions
- Should Staff reference Department at this stage? (If taxonomy not ready, add nullable foreign keys now or postpone.)
- Decide if user creation auto-generates staff or vice versa; initial approach can keep simple “create staff -> optionally link existing user”.
- Determine logging solution: reuse existing `activity_logs` table (add migration) or rely on Spatie activity log package later.

Proceed with implementation once schema/seed decisions confirmed with the user.

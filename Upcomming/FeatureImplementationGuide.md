# Feature Implementation Reference

Use this document when wiring new modules so UI behaviours stay consistent with the boilerplate.

## Layout & Navigation
- Main wrapper: `resources/js/layouts/AppLayout.vue` delegates to `resources/js/layouts/app/AppSidebarLayout.vue` (sidebar + main content) or auth layouts.
- Sidebar UI lives in `resources/js/components/AppSidebar*.vue`; hide/adjust navigation items there.
- Background gradients/glass effects come from Tailwind overrides in `resources/css/app.css` (see `.liquidGlass-wrapper`, `.app-sidebar`).

## Buttons & Actions
- Reuse `GlassButton` (`resources/js/components/GlassButton.vue`). Variant styling (`btn-variant-*`) is defined in `resources/css/app.css` around the `.btn-glass` block.
- For icon-only controls, follow the inline link/button pattern already used in `Staff/Index.vue` and keep `title` + `.sr-only` text for accessibility.

## Cards, Toolbars, Tables
- `GlassCard` (`resources/js/components/GlassCard.vue`) is the default card shell; pass `variant="lite"` for lighter glass.
- `ResourceToolbar` (`resources/js/components/ResourceToolbar.vue`) provides title, description, and optional create/export/print buttons.
- Table wrappers generally include:
  1. Filters/search inputs.
  2. `GlassCard` or bordered div around a `<table>`.
  3. `<Pagination :links="paginationLinks" />` (see below).

## Pagination
- Vue component: `resources/js/components/Pagination.vue`.
  - Update the class string on the `link.active` branch to change the highlight colour globally (e.g., swap `bg-indigo-600` for a purple class).
- Front-end pattern and backend expectations live in `Upcomming/PaginationImplementationGuide.md`.
- Tailwind colour utilities are available through `resources/css/app.css` and `tailwind.config.ts` if new shades are required.

## Print
- Detailed steps and CSS conventions: `Upcomming/PrintImplementationGuide.md`.
- Staff and Users modules show the landscape (index) and portrait (detail) implementations.

## Export / Download Center
- Exports index page lives in `resources/js/pages/Exports/Index.vue` with matching controller logic in `app/Http/Controllers/DataExportController.php`.
- Module-specific exports reuse `App\Support\Exports\HandlesDataExport` with config entries in `app/Support/Exports/ExportConfig.php`.
- `ResourceToolbar` currently triggers CSV export via its `@export` event; hook custom logic into that slot.

## Notifications
- Backend routes: `routes/web.php` registers `notifications.index`, `notifications.markAsRead`, and `notifications.markAllRead` handled by `app/Http/Controllers/NotificationController.php`.
- Persistence: Laravel's default `notifications` table (migration: `2025_01_01_240000_create_notifications_table.php`).
- Front-end dropdown: `resources/js/components/NotificationBell.vue` (auto-polling, badge count, "view all" link). This component is injected into the header via `AppSidebarLayout`.
- Full-page view: `resources/js/pages/Notifications/Index.vue` lists notifications, supports mark-single and mark-all actions, and links to settings.
- To emit notifications from modules, use Laravel's `Notification` facade or the `Notifiable` trait on models (see Geraye services under `app/Services/...` for patterns).

## Email & Password Reset
- Mail transport is configured through `.env` (`MAIL_MAILER`, `MAIL_HOST`, etc.) and `config/mail.php`. Local development can use tools such as Mailpit; production should supply SMTP credentials.
- Password reset emails come from Laravel's built-in notifications (`Illuminate\Auth\Notifications\ResetPassword`). Feature tests live in `tests/Feature/Auth/PasswordResetTest.php` and can be mirrored when adding new mail flows.
- Custom notifications should extend `Illuminate\Notifications\Notification` and may implement `ShouldQueue`. Place them under `app/Notifications` and dispatch via `Notification::send()` or `$user->notify()`.
- Remember to update `config/auth.php` notification channels or `.env` mail settings before deploying to ensure production SMTP works out of the box.

## CRUD Pattern
- Controllers follow the Staff/User examples in `app/Http/Controllers`. Requests live under `app/Http/Requests` and resources/transformers under `app/Http/Resources`.
- Keep index actions returning paginated Inertia responses (`->paginate($perPage)->withQueryString()`). Use `->through()` to shape payloads before Inertia.
- Create/edit forms use the Vue pages inside each module folder (`resources/js/pages/<Module>/<Action>.vue`).

## Forms & Validation
- Confirmation prompts are powered by `confirmDialog` from `resources/js/lib/confirm.ts` and `ConfirmModal` defined in `resources/js/components/ConfirmModal.vue` (instantiated inside `AppLayout`).
- Toast/flash messages use `resources/js/components/Toast.vue` which listens for Inertia flash props.

## Sidebar & Navigation Styling
- Sidebar colours and spacing are defined in `resources/js/components/AppSidebar*.vue` plus the `.app-sidebar` block in `resources/css/app.css`.
- To tweak hover states or add badges, extend the markup in `AppSidebarHeader.vue`, `AppSidebar.vue`, and the `Nav*` components inside `resources/js/components`.

## Theme & Colour Tokens
- Global CSS variables and gradients are centralised in `resources/css/app.css`.
- Tailwind customisations (including additional colours) sit in `tailwind.config.ts`.
- Update both when introducing new palette shades so utilities and custom classes stay in sync.

## Reference Checklists
- Pagination: `Upcomming/PaginationImplementationGuide.md`.
- Printing: `Upcomming/PrintImplementationGuide.md`.
- General module planning: `Upcomming/GerayeFeatureMigrationTasks.md`.

Keep this document updated whenever shared components change so future phases can align quickly.

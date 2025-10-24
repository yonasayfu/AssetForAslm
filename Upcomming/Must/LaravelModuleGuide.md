# AssetManagement Laravel Flow & Feature Integration Guide

This short note recaps how the current stack is wired so you can extend it confidently when new modules arrive (Assets, Maintenance, etc.).

---

## Request → Response Flow (Laravel + Inertia)

1. **Route** – `/staff` or `/users` routes live in `routes/web.php`. Routes inside the authenticated group run through the permission middleware (e.g. `permission:staff.view`).
2. **Controller** – e.g. `StaffController@index` gathers data (with filters, pagination, stats) and returns an Inertia response:  
   ```php
   return Inertia::render('Staff/Index', [...props]);
   ```
3. **Inertia bridge** – `resources/js/app.ts` bootstraps the Vue app and renders the page component referenced by the controller (`resources/js/pages/Staff/Index.vue`).
4. **Layout** – every page declares `<AppLayout>` so toast/confirm/modal providers and the header/sidebar composition wrap the content.
5. **Vue components** – the page consumes the controller props, renders lists/forms, and uses shared components (GlassCard, useTableFilters, ActivityTimeline, etc.).

When building a new module you repeat the same route → controller → inertia → Vue flow.

---

## Module Pattern Checklist

Each CRUD module follows the same pieces:

- **Migration**: create the table (`database/migrations/*`). Index the columns that appear in filters (`status`, `location_id`, etc.).
- **Factory & Seeder**: seed sample data (`database/factories`, `database/seeders`). The base seeder already creates demo staff/users; copy the pattern for assets.
- **Model**: define fillable fields, relationships, and optional traits like `RecordsActivity`.
- **Form Requests**: use `FormRequest` classes for validation (see `UserStoreRequest`, `StaffUpdateRequest`).
- **Controller**: use the service/controller pairing from Phase 2 as a template (thin controller, `useTableFilters` support, eager-loading relationships).
- **Policies & Permissions**: add permission slugs in `RolePermissionSeeder.php`, register route middleware (`permission:assets.view`), and make sure Inertia share (`HandleInertiaRequests`) exposes the new `auth.can` flag so the sidebar/header can hide or show links.
- **Vue pages**: place files under `resources/js/pages/<Module>/`. Reuse the shared components and composables.

---

## List Pages + `useTableFilters`

`resources/js/composables/useTableFilters.ts` keeps list filters in sync with the query string:

```ts
const { search, perPage, sort, direction, apply, toggleSort } = useTableFilters({
    route: '/assets',                   // the URL to hit
    initial: props.filters,             // data from controller
    extra: () => ({ status: status.value || undefined }), // any extra params
});
```

**Implementing a new list page**:
1. Controller: read filter inputs (`$search`, `$perPage`, `$sort`, etc.), apply them to the query, and call `paginate($perPage)->withQueryString()`.
2. Pass the filters back to Vue (`'filters' => ['search' => $search, ...]`).
3. In Vue: import `useTableFilters`, bind `search`, `perPage`, `toggleSort`, and drop in the shared `<Pagination :links="paginationLinks" />`.
4. Add `.search-glass` wrapper for the input to keep the glass styling.

---

## Global Search integration

The header/toolbar already renders `<GlobalSearch />`. To add a module to the search results:

1. Update `app/Http/Controllers/GlobalSearchController.php`:
   ```php
   if ($request->user()->can('assets.view')) {
       $assetMatches = Asset::query()
           ->where('name', 'like', "%{$term}%")
           ->orWhere('serial_number', 'like', "%{$term}%")
           ->limit(5)
           ->get();

       $results = $results->merge($assetMatches->map(function (Asset $asset) {
           return [
               'type' => 'Asset',
               'category' => 'Inventory',
               'title' => $asset->name,
               'description' => $asset->serial_number,
               'url' => route('assets.edit', $asset),
               'icon' => 'package',
           ];
       }));
   }
   ```
2. Ensure the permission slug (`assets.view`) is seeded and the user actually has it.
3. No front-end changes are needed—the modal groups results by `category` automatically.

---

## Activity Logging

- `app/Models/Concerns/RecordsActivity` hooks into model events and writes to `activity_logs`.
- Models define `$activityLogAttributes` to capture meaningful columns.
- Staff/User edit pages already show historical entries via `ActivityTimeline.vue`.

**To log a new module**:
1. `use RecordsActivity;` on the model.
2. Populate `$activityLogAttributes` / `$activityLogLabel`.
3. For custom events (e.g. status change via a service class), call:
   ```php
   ActivityLog::record(auth()->id(), $asset, 'status.changed', 'Status updated', [
       'before' => ['status' => $old],
       'after'  => ['status' => $new],
   ]);
   ```
4. Surface the timeline in your edit view by passing `ActivityLogResource::collection($asset->activityLogs()->with('causer')->latest()->take(20)->get())` to Inertia, then drop `<ActivityTimeline :entries="activity" />` into the Vue page.

---

## Extending the Sidebar/Header

- **Sidebar**: `resources/js/components/AppSidebar.vue` reads `auth.can` and builds navigation items. Add a new link after seeding permissions.
- **Header**: `AppSidebarHeader.vue` renders the breadcrumb, search, and contextual actions (you can slot a “New Asset” button via `<template #actions>` in the page).
- **`HandleInertiaRequests`**: whenever you add a new permission slug, expose it via the `auth.can` array so the UI can react.

---

## Putting it together for a new Module (Example: Assets)

1. Create migration + model + factory.
2. Seed demo assets in `DatabaseSeeder`.
3. Add permissions (`assets.view`, `assets.create`, etc.) to `RolePermissionSeeder`, assign to roles.
4. Build `AssetController` (index/create/edit/delete) + form requests; remember to return stats, filters, and `ActivityLogResource`.
5. Create Vue pages in `resources/js/pages/Assets/`, using `useTableFilters` for index and `ActivityTimeline` for edit.
6. Register routes under `/assets` with appropriate middleware.
7. Update global search / sidebar / tasks docs as needed.

By following this checklist the new module will feel identical to the Staff/User experience: consistent filters, reusable lists, search integration, and activity history out-of-the-box.

Happy building!

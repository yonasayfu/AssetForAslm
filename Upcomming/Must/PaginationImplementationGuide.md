# Pagination Implementation Guide

This guide captures how list views should handle pagination across AssetManagement, mirroring the Geraye pattern.

## Baseline Behaviour
- Default to 5 records per page (`per_page=5`) so small datasets stay legible without scrolling.
- Allow users to increase page size to 10, 25, 50, or 100 via the per-page dropdown.
- Persist pagination selections via the query string so the backend applies the same filters and page size.
- All list endpoints must accept `per_page` and `page` parameters and return Inertia pagination links/meta.

## Vue Pattern
1. Use the `useTableFilters` composable to manage `perPage`, `search`, sort, and other filters.
2. Initialise `per_page` in the `initial` object to `props.filters?.per_page ?? 5` for consistency.
3. Render the per-page selector with the shared Tailwind classes and the five options (5/10/25/50/100).
4. Include `<Pagination :links="paginationLinks" />` directly beneath the table, using links provided by Laravel.
5. When building query strings for exports or prints, include the selected `perPage` value to respect the current view.

## Backend Expectations
- Controllers should read `per_page` from the request and clamp the value to the allowed set (5,10,25,50,100) to prevent abuse.
- Default page size in controllers/services should match the frontend (5) when no value is provided.
- Reuse shared query scopes/composables where possible so future modules inherit consistent behaviour.

## Reference Implementations
- Staff list (`resources/js/pages/Staff/Index.vue` + corresponding controller) shows the full filter + pagination pattern.
- Users list mirrors the same approach; use it when wiring new admin modules.
- Download center/exports now share the same defaults and dropdown (`resources/js/pages/Exports/Index.vue`).

## Checklist For New Modules
- [ ] Initialise `useTableFilters` with `per_page: props.filters?.per_page ?? 5` and expose `perPage` from the composable.
- [ ] Render the per-page dropdown with 5/10/25/50/100 options.
- [ ] Ensure the backend resource returns paginated JSON with `data`, `links`, and `meta` entries for Inertia.
- [ ] Include the shared `Pagination` component at the bottom of the table.
- [ ] Pass the selected `perPage` into any export or print helpers so auxiliary actions use the same dataset.

Following this checklist keeps pagination aligned with the Geraye boilerplate and avoids regressions as modules expand.

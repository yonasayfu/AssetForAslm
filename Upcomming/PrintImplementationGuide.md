# Print Implementation Guidelines

This project keeps the browser print dialog inside the existing page instead of opening a new tab. Follow the steps below whenever you enable printing for a new module.

## 1. Triggering the Print Dialog
- Use the module page to define a `triggerPrint()` helper that wraps `window.print()` and temporarily overrides `document.title`. Copy the pattern from `resources/js/pages/Staff/Index.vue` or `resources/js/pages/Users/Index.vue`.
- Always restore the original title immediately after `window.print()` returns so normal navigation keeps the expected page title.
- Connect the helper to the `ResourceToolbar` `@print` event for list pages, and to any inline buttons on detail pages.
- For routes that support `?print=1`, call `triggerPrint()` inside the existing `onMounted` block once the page is ready.

## 2. Orientation Standards
- List/Index pages: `@page { size: A4 landscape; }` with ~1.5 cm margins. See `resources/js/pages/Staff/Index.vue` for the exact rule set.
- Detail/Show pages: `@page { size: A4 portrait; }` using the same margins; `resources/js/pages/Staff/Show.vue` is the reference implementation.

## 3. Print Header Block
- Insert a `print:block` wrapper above the main content that includes:
  1. `/images/asset-logo.svg` (swap if the module needs a different mark).
  2. A heading describing the module (for example, "Staff Directory" or "Asset Profile").
  3. A human-friendly timestamp derived from the shared `printTimestamp` formatter.
- Hide non-essential metrics, filters, and action columns with `print:hidden` so only the data table or key record details render.

## 4. Table and Card Styling
- Apply the `.print-table` class to primary tables and reuse the shared CSS rules (bordered cells, reduced padding, white background).
- Use `.print:shadow-none`, `.print:bg-white`, and `.print:border` on cards/sections to neutralise glass or gradient effects.
- Keep status badges readable by allowing the neutral colour overrides defined in the show-page print styles.

## 5. Layout Adjustments
- `AppSidebarLayout` hides the sidebar and gradient shapes during print (`resources/js/layouts/app/AppSidebarLayout.vue`). Always use this layout for modules that need print support.
- In the page-level print block, add overrides for `.app-sidebar { display: none !important; }`, `.min-h-screen { min-height: auto !important; }`, and `body { height: auto !important; }` to prevent stray blank pages. Mark action toolbars/columns with `print:hidden`.
- Apply `main { page-break-after: avoid; }` (or `page-break-inside: avoid` on key wrappers) when the layout uses large flex containers so the browser does not insert redundant trailing pages.

## 6. Central Reference
- Staff module:
  - Index (landscape) baseline: `resources/js/pages/Staff/Index.vue`.
  - Detail (portrait) baseline: `resources/js/pages/Staff/Show.vue`.
- Users module mirrors the same conventions; inspect it if you need another example.

Apply these conventions to every upcoming module before enabling the “Print Current” control to keep output consistent with the Geraye baseline.

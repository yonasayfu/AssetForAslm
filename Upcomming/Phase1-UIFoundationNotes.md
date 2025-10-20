# Phase 1 – Application Shell & Styling Notes

## Visual System
- Added liquid-glass utility classes, button styling, and sidebar surface tweaks in `resources/css/app.css` so any component can opt into the shared macOS-style sheen.
- Sidebar and main canvas now render against gradient backgrounds with floating highlights via `resources/js/layouts/app/AppSidebarLayout.vue` and `resources/js/components/AppHeader.vue`.
- `resources/js/components/AppSidebarHeader.vue` wraps the toolbar in a glass card and exposes an `actions` slot for page-level CTAs.

## Reusable Components
- New `resources/js/components/GlassCard.vue` and `resources/js/components/GlassButton.vue` centralize the translucent treatment for cards and actions.
- Global flash feedback lives in `resources/js/components/Toast.vue`; the component auto-closes based on severity but can be paused on hover.
- Confirmation flows use `resources/js/components/ConfirmModal.vue` plus the `useConfirm` composable (`resources/js/composables/useConfirm.ts`). The provider is wired inside `resources/js/layouts/AppLayout.vue`.

## Layout Upgrades
- Auth experience now supports multiple variants (glass, split, card, simple) through `resources/js/layouts/AuthLayout.vue` with implementations in `resources/js/layouts/auth/`.
- Dashboard header and auth screens consume `GlassCard` for consistent styling, and `AppLayout` drops a `<Toast />` + confirmation modal wrapper so every page inherits them automatically.

## Usage Tips
- Call the confirmation dialog anywhere in the app with:
  ```ts
  import { useConfirm } from '@/composables/useConfirm';

  const { confirm } = useConfirm();
  const accepted = await confirm({ title: 'Remove asset?', message: 'This cannot be undone.' });
  ```
- For actions that should respect the new style, wrap content with `<GlassCard>` or use `<GlassButton>` instead of raw `Button` when you want the frosted accent.

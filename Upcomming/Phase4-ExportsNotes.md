# Phase 4 - Export, Print & File Handling Notes

## Delivered
- Central export trait + download center (`app/Support/Exports/*`, `app/Http/Controllers/DataExportController.php`, `resources/js/pages/Exports/Index.vue`).
- Unified PDF layout in `resources/views/pdf-layout.blade.php` with Asset Suite branding.
- Vue `ResourceToolbar` for consistent export/print actions on list pages.
- Staff/User modules wired to CSV export and print-current endpoints.
- Staff/User show pages include back/edit actions and pop-up print previews.
- Document/image upload UX via `FileUploadField.vue`; staff avatars stored under `storage/app/public/staff/avatars`.
- Storage helpers (`App\Support\Storage\StoragePath`) and `data_exports` tracking table for download center entries.
- Staff download center navigation entry and tests (`tests/Feature/StaffAvatarTest.php`, `tests/Feature/DataExportTest.php`).

## Manual Test Checklist
1. Visit `/staff`; use toolbar buttons to export CSV and print current view (ensure downloads succeed).
2. Visit `/users`; verify export & print actions honour filters/search.
3. Create a new staff member with an avatar image; confirm file saved in `storage/app/public/staff/avatars` and thumb displays on edit view.
4. Edit same staff member, toggle "Remove" on avatar; ensure file deleted and placeholder shown.
5. Queue multiple exports; check `/exports` download center lists entries, allows download & delete, and respects ownership.
6. Attempt to access another users export URL; should return 403.
7. Generate PDF printouts and confirm header/footer branding.

## Follow-ups / Parking Lot
- Finalize activity log stream for exports once Phase 2 audit logging closes.
- Confirm retention job (90-day prune) timing before boilerplate snapshot.

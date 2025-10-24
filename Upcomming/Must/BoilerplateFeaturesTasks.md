# Boilerplate Features Task Tracker

This document tracks the implementation progress of additional "must-have" features for the AssetManagement boilerplate.

## 1. Two-Factor Authentication (2FA) for Login

- [x] Enable Fortify's 2FA features in `config/fortify.php`.
- [x] Build UI for 2FA setup (displaying QR code, confirming setup code).
- [x] Build UI for 2FA challenge during login.
- [ ] Test 2FA setup and login with Mailpit (for recovery codes if emailed).

## 2. Comprehensive Notification Management

- [x] Define various notification types (e.g., `NewAssignmentNotification`, `DataExportReadyNotification`).
- [x] Choose notification channels (database for in-app, email for external).
- [x] Build UI for displaying in-app notifications (e.g., a notification bell/dropdown).
- [x] Build UI for managing notification preferences (e.g., "email me for X, don't email me for Y").

## 3. Enhanced Activity Logging & Auditing

- [ ] Identify critical actions across the application (e.g., user creation/update/deletion, role changes, data access).
- [ ] Implement logging for these actions using the existing `RecordsActivity` trait or custom log entries.
- [ ] Build UI for viewing activity logs (e.g., a dedicated "Audit Log" page with filters).

## 4. User Impersonation (Admin "Login As")

- [ ] Create a route and controller action for impersonation.
- [ ] Implement logic to switch user sessions securely.
- [ ] Build UI for admins to initiate and end impersonation.
- [ ] Ensure robust security checks (only super-admins can impersonate, cannot impersonate other super-admins).

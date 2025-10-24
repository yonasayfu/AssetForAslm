# Boilerplate Features Plan

This document outlines additional "must-have" features to enhance the AssetManagement project as a robust boilerplate for future applications.

## 1. Two-Factor Authentication (2FA) for Login

*   **Description**: Implement 2FA to secure user logins. Users will be prompted for a second factor (e.g., a code from an authenticator app) after entering their password.
*   **Why it's a must-have**: Enhances security significantly, especially for admin/staff accounts. It's a standard security practice.
*   **Laravel Integration**: Laravel Fortify provides robust, built-in 2FA support. The `TwoFactorAuthenticatable` trait is already on the `User` model, and necessary database columns (`two_factor_secret`, `two_factor_recovery_codes`) exist.
*   **Implementation Steps**:
    1.  Enable Fortify's 2FA features in `config/fortify.php`.
    2.  Build UI for 2FA setup (displaying QR code, confirming setup code).
    3.  Build UI for 2FA challenge during login.
    4.  Integrate with an authenticator app (e.g., Google Authenticator).
*   **Relation to MFA Reset**: Distinct from MFA password reset. 2FA is for *login security*, MFA reset is for *password recovery*.

## 2. Comprehensive Notification Management

*   **Description**: Implement a robust system for in-app and email notifications for users based on various application events.
*   **Why it's a must-have**: Keeps users informed about important events (e.g., new assignments, system alerts, data export completion). Improves user experience and operational awareness.
*   **Laravel Integration**: Laravel has a powerful built-in notification system.
*   **Implementation Steps**:
    1.  Define various notification types (e.g., `NewAssignmentNotification`, `DataExportReadyNotification`).
    2.  Choose notification channels (database for in-app, email for external).
    3.  Build UI for displaying in-app notifications (e.g., a notification bell/dropdown).
    4.  Build UI for managing notification preferences (e.g., "email me for X, don't email me for Y").

## 3. Enhanced Activity Logging & Auditing

*   **Description**: Expand the existing activity logging to cover more critical user and system actions, providing a comprehensive audit trail.
*   **Why it's a must-have**: Essential for security, compliance, and debugging. Knowing who did what, when, and where.
*   **Laravel Integration**: The `RecordsActivity` trait and `ActivityLog` model are already in place, providing a solid foundation.
*   **Implementation Steps**:
    1.  Identify critical actions across the application (e.g., user creation/update/deletion, role changes, data access).
    2.  Implement logging for these actions using the existing `RecordsActivity` trait or custom log entries.
    3.  Build UI for viewing activity logs (e.g., a dedicated "Audit Log" page with filters).

### Activity Logging Matrix

| Area | Events Covered | Notes |
| --- | --- | --- |
| Users | Create/update/delete, role & permission changes, staff linking | `User` model uses `RecordsActivity`; controller logs role/permission diffs |
| Staff | Create/update/delete, user association updates | `Staff` model uses `RecordsActivity`; `syncStaffAssignment` logs linking moves |
| Notification Preferences | Enable/disable per channel | `UserNotificationPreference` model records changes so support can audit opt-ins |
| Mailbox Messages | Ingested, status updates (processed, assigned) | `MailboxMessage` records subject/status/environment; ingestion job & UI updates set metadata |
| Roles & Permissions | Role CRUD, permission assignments | `RoleManagementController` records operations |
| Authentication | Impersonation start/stop, MFA setup/reset | Logged via impersonation routes and 2FA flows (see Security section) |

> Keep this table updated as new modules adopt activity logging so the audit trail stays comprehensive.

## 4. User Impersonation (Admin "Login As")

*   **Description**: Allow administrators to temporarily "log in as" another user without knowing their password, for debugging or support purposes.
*   **Why it's a must-have**: Greatly simplifies support and troubleshooting, allowing admins to experience the application from a user's perspective.
*   **Laravel Integration**: Can be implemented with a custom middleware and session manipulation, or using a package like `Laravel-Impersonate`.
*   **Implementation Steps**:
    1.  Create a route and controller action for impersonation.
    2.  Implement logic to switch user sessions securely.
    3.  Build UI for admins to initiate and end impersonation.
    4.  Ensure robust security checks (only super-admins can impersonate, cannot impersonate other super-admins).




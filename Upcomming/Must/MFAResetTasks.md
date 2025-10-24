# MFA Password Reset Tasks

This checklist tracks the implementation of the new self-service, multi-factor password reset flow.

## Phase 1: Database and Configuration
- [x] Create migration to add `recovery_email` and `phone_number` to the `users` table.
- [x] Update `User` model to include the new fields.
- [x] Add configuration for the chosen transactional email service to `config/services.php` and `.env.example`.

## Phase 2: Backend Logic
- [x] Modify "Forgot Password" logic to trigger the MFA flow instead of sending a reset link.
- [x] Implement logic to generate, store, and send a 6-digit verification code to the user's recovery email.
- [x] Create the controller method and validation logic to verify the code submitted by the user.

## Phase 3: Frontend UI
- [x] Build the "Enter Code" Vue page with a polished, modern UI.
- [x] Build the final "Reset Password" Vue page where the user sets their new password.
- [x] Ensure all new pages are responsive and user-friendly.

## Phase 4: Finalization & Polish
- [x] Apply the new UI design to the developer-only Mailbox module for consistency.
- [x] Update the manual testing guide to cover the new MFA flow.
- [x] Review and remove any code that is no longer needed.

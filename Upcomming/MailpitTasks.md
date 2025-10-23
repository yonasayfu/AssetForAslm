# Mailpit Integration Tasks

This checklist tracks the end-to-end work for pairing AssetManagement with Mailpit and delivering the in-app mailbox experience.

## Phase 0 - Discovery & Requirements
- [x] Confirm security/compliance requirements (data retention, access control, TLS, auditing).
- [x] Decide Mailpit deployment model for each environment (local binary/Docker, cloud sidecar/VM).
- [x] Finalize integration method (webhook vs polling) and define the payload/response contract.
- [x] Approve UX scope for the Gmail-lite mailbox (views, filters, audit trail, reset flow experience).

## Phase 1 - Infrastructure & Tooling
- [x] Script local Mailpit startup and document how it runs alongside `php artisan serve`.
- [x] Extend the shared `.env` template with `MAILPIT_*` keys plus retention/secret settings.
- [x] Document the production Mailpit deployment plan, including network, security, and scaling.
- [x] Establish monitoring/logging (Mailpit metrics, Laravel logs, alerting hooks).

## Phase 2 - Laravel Mailbox Module Foundations
- [x] Design database schema/storage strategy for messages, recipients, attachments, and events.
- [x] Implement webhook/polling controller with queued job to ingest Mailpit payloads.
- [x] Add mail processing services (HTML/plain extraction, attachment handling, retention cleanup).
- [ ] Cover ingestion flow with unit/integration tests.

## Phase 3 - UI/UX Implementation (Vue Inside AssetManagement)
- [ ] Build inbox list with filters (status, environment, user, date range).
- [ ] Implement message detail view (HTML/plain toggle, headers, attachments, audit actions).
- [ ] Wire password reset workflow UI (open reset link, mark processed, optional MFA prompts).
- [ ] Add real-time updates or polling for new messages and ensure responsive layout.
- [ ] Enforce mailbox access control (roles/permissions).

## Phase 4 - Password Reset Integration & QA
- [ ] Update password reset flow to log interactions with the Mailbox module.
- [ ] Write automated feature tests covering reset end-to-end with Mailpit fixtures.
- [ ] Provide helper commands/scripts for QA to review latest messages.
- [ ] Document rollback/cleanup steps (purge messages, rotate webhook secrets).

## Phase 5 - Production Hardening & Launch
- [ ] Configure TLS/HTTPS for Mailpit and Laravel endpoints.
- [ ] Finalize backup/retention policies and automated cleanup jobs.
- [ ] Execute load/security testing; verify restart/failover scenarios.
- [ ] Prepare deployment checklist/runbook for Laravel Cloud + Mailpit sidecar.
- [ ] Plan staff training/demo and collect post-launch feedback.


Access control: Who should see captured emails (admins only, support staff, per-project)? Do we need role-based permissions or MFA?
Data retention: How long can we store reset emails? Do we purge after X days or on demand?
Confidentiality: Are reset emails sensitive enough to require encryption at rest, masking user data, or restricting logs?
Transport security: In production, do we need HTTPS/TLS everywhere (Mailpit UI, webhook traffic, SMTP TLS)?
Auditing: Should we log who viewed/reset/marked emails, and keep an audit trail for compliance?
Regulations: Any org requirements (GDPR, company policies) that dictate storage location, logging, or user consent?

Here’s a professional baseline we can adopt for the Mailpit integration—mirrors what larger teams typically enforce:

Access control – Only platform admins and designated support engineers can view captured mail. We’ll gate the Mailbox module behind Laravel roles/permissions and logins (optional MFA if you already use it). Regular staff reset their passwords through the standard self-service flow; they never see Mailpit.

Data retention – Keep reset emails for a short window (e.g., 7 days) so support can troubleshoot, then auto-purge via a scheduled Laravel job. Offer an admin “purge now” action for urgent cleanup.

Confidentiality – Passwords are never in email; Laravel continues hashing resets in the database using Argon2/bcrypt. For stored messages we’ll encrypt at rest (Laravel’s APP_KEY encryption for body + attachment paths) and avoid logging any raw content outside the mailbox tables.

Transport security – In production we’ll require TLS/HTTPS everywhere: Mailpit HTTP behind HTTPS (reverse proxy or built-in cert), webhook traffic over HTTPS with a shared secret or signed HMAC, and STARTTLS for SMTP if the reset flow demands it. Local dev can stay on plain HTTP since it’s localhost.

Auditing – Record who viewed, downloaded, or marked a message as processed (timestamp + user ID) so you have an audit trail. Laravel observers or activity-log package can handle this.

Regulatory stance – No explicit GDPR/industry mandates right now, so we’ll keep data in your chosen region, respect the 7‑day retention, and document that messages are only used for internal support/testing. If you later need to handle subjects with PII, we can revisit masking or longer encryption keys.


Cleanup & Prep

Removed the legacy apps (AssetManagement/mailcatcher, AssetManagement/baseProject) so the repo now only contains the core AssetManagement codebase.
Recreated Mailpit launch scripts in AssetManagement/tools/mailpit/start-mailpit.ps1 and AssetManagement/tools/mailpit/start-mailpit.sh for Windows/macOS use.
Updated docs to match the new layout: AssetManagement/Upcomming/IntroToMailpit.md:37–112, AssetManagement/Upcomming/EmailImplementationGuide.md:17–36, and refreshed the roadmap checklist in AssetManagement/Upcomming/MailpitTasks.md:1–35.
Please Test

Run powershell -ExecutionPolicy Bypass -File tools/mailpit/start-mailpit.ps1, then php artisan serve, trigger a password reset, and confirm the email appears at http://127.0.0.1:8025.
Next Step

I’m queued to start Phase 2 by designing the mailbox database schema and webhook ingestion pipeline once you’re ready.
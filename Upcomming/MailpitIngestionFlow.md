# Mailpit Webhook Ingestion Flow

This plan describes how AssetManagement consumes Mailpit webhooks, persists messages to the mailbox tables, and maintains reliability.

## Overview
1. Mailpit receives an email and immediately POSTs a JSON payload to the Laravel webhook endpoint (`POST /mailpit/webhook`).
2. Laravel verifies the request signature/token, enqueues an ingestion job, and returns HTTP 204 quickly.
3. The job normalizes the payload, stores the message/recipients/attachments, schedules attachment downloads, and records audit events.
4. Optional recovery command (`php artisan mailpit:sync`) polls Mailpit's REST API to backfill messages if a webhook is missed.

## Request Validation
- **Route**: `Route::post('/mailpit/webhook', [MailpitWebhookController::class, 'store'])->middleware(['throttle:mailpit', 'signed.mailpit']);`
- **Middleware**:
  - `throttle:mailpit` rate-limits incoming requests.
  - Custom `VerifyMailpitSignature` middleware validates `X-Mailpit-Token` header against `MAILPIT_WEBHOOK_TOKEN` and optionally checks the request IP against an allowlist.
  - `EnsureJsonPayload` ensures `Content-Type: application/json`.
- **Controller**:
  - Validate payload structure using form request or DTO.
  - Dispatch `IngestMailpitMessage` job with the raw payload and environment context (derived from header or `mailpit.label`).
  - Respond `204 No Content` on success; `4xx` on invalid signature; `5xx` triggers Mailpit retry.

## Ingestion Job Responsibilities
- Accepts `array $payload`, `string $environment`.
- Creates/updates `MailboxMessage` (idempotent via `mailpit_id`).
- Upserts recipients:
  - Sender stored as recipient with type `from`.
  - To/Cc/Bcc arrays mapped to rows with dedupe on `message_id` + `email` + `type`.
- Stores bodies and headers:
  - Sanitizes HTML (optional) before encrypting.
  - Generates preview snippet from text/plain version.
- Handles attachments:
  - For inline attachments, store metadata even if not downloaded.
  - For file attachments, enqueue `DownloadMailpitAttachment` jobs that call Mailpit `GET /api/v1/message/{id}/attachment/{hash}` and write to disk.
- Emits `MailboxEvent` with type `ingested` and payload describing message origin (mailpit label, remote IP).
- Dispatches `EnforceRetention` job nightly to delete expired messages.

### Error Handling
- Wrap DB operations in transaction; on failure, mark job as failed and `retry until` (e.g., 5 attempts).
- Log structured error with `mailpit_id` for troubleshooting.
- Use `withoutOverlapping` job middleware to prevent duplicate ingestion of the same `mailpit_id`.

## Attachment Download Job
- Accepts `message_id`, `attachment_metadata`.
- Fetches binary via Mailpit API using HTTP Basic or token auth.
- Writes file to `Storage::disk(MAILBOX_STORAGE_DISK)->put("mailbox/{message}/{filename}", $stream);`
- Updates `MailboxAttachment` record with `path`, `checksum`.
- Records `MailboxEvent` with type `attachment_saved`.

## Recovery Command
- `php artisan mailpit:sync {--since=}` to backfill messages:
  - Calls Mailpit API `/api/v1/messages` with pagination.
  - Skips messages already stored (`mailpit_id` check).
  - Optionally deletes messages no longer in Mailpit after retention window.

## Configuration
- `.env` keys:
  - `MAILPIT_WEBHOOK_TOKEN` (header check).
  - `MAILPIT_HTTP_URL` for attachment API base.
  - `MAILPIT_WEBHOOK_SIGNATURE_KEY` if using HMAC (future).
  - `MAILBOX_STORAGE_DISK`, `MAILBOX_RETENTION_DAYS`.
- `config/mailbox.php` (new config file) to centralize defaults (retention, label mapping, disk, queue names).

## Queue & Performance
- Use dedicated queue (`mailbox`) to keep ingestion separate from other jobs (`QUEUE_MAILBOX=mailbox`).
- Configure worker concurrency to handle burst traffic (e.g., 5 workers).
- For large bodies, consider streaming attachments and storing bodies compressed (Laravel built-in `CompressedJsonCast` or manual gzip).

## Testing Strategy
- **Feature tests**: fake webhook request with sample payload, assert DB records created.
- **Job tests**: ensure idempotency, attachment download logic, event creation.
- **Command tests**: run `mailpit:sync` with HTTP fake to verify backfill.
- **Integration tests**: spin up local Mailpit, trigger email from password reset, assert end-to-end ingestion using PHPUnit and HTTP client.

## Open Questions
- Do we need to redact certain PII in stored bodies? (follow-up with stakeholders).
- Should we support multi-tenant labeling if multiple apps feed the same Mailpit instance? (future enhancement).

Once these pieces are in place, development can move to writing migrations, controllers, jobs, and tests according to this plan.

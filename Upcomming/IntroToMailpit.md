# Intro to Mailpit & Integration Plan

## What is Mailpit?
Mailpit is an open-source email testing tool written in Go. It acts as a lightweight SMTP server that captures messages and provides an HTTP interface (web UI and REST API) to inspect them. Developers use it to verify email flows (password resets, notifications) without sending messages to real mail providers.

### Key Capabilities
- Accepts SMTP traffic on a configurable port (default `1025`).
- Offers a web UI for viewing captured emails (default HTTP port `8025`).
- Exposes REST/JSON endpoints and webhooks so other applications can ingest stored messages.
- Stores emails on disk or in an embedded database with tagging/search support.
- Ships as a single binary for Windows/macOS/Linux and as Docker images.

## Why pair Mailpit with AssetManagement?
- AssetManagement needs a reliable way to capture password-reset emails during development, QA, and production support without relying on third-party SMTP providers.
- Rather than re-implement SMTP handling in PHP, we let Mailpit manage the protocol and message storage while AssetManagement focuses on UI, auditing, and staff workflows.
- This modular approach keeps deployment flexible: Mailpit can run locally as a binary, or in the cloud as a sidecar service, while AssetManagement remains a standard Laravel app.

## Integration Overview
1. **SMTP Capture**: Laravel's mail settings (`MAIL_HOST`, `MAIL_PORT`) point to Mailpit. All outgoing emails (e.g., password reset links) are delivered to Mailpit instead of an external provider.
2. **Message Ingestion**: AssetManagement retrieves captured emails from Mailpit via webhook callbacks or polling against Mailpit's REST API. Messages are stored in AssetManagement's database/storage for history and search.
3. **Mailbox UI**: A new module inside AssetManagement (built with Vue/Blade) provides a Gmail-lite inbox so staff can view reset messages, follow links, mark messages processed, and audit activity.
4. **Security & Retention**: Mailpit's ports remain private; the Mailbox module enforces Laravel auth/permissions, and housekeeping jobs purge aged messages based on policy.

### Security Baseline (Approved)
- **Access control**: Only platform admins and designated support engineers can view captured mail. The Mailbox module sits behind Laravel's auth/role system, with optional MFA where available.
- **Data retention**: Store reset emails for 7 days by default, purge automatically via a scheduled job, and provide an admin-triggered "purge now" action for urgent cleanup.
- **Confidentiality**: Continue hashing passwords in the main user table; store captured email bodies/attachments encrypted at rest using Laravel's standard encryption. Avoid logging raw content outside mailbox tables.
- **Transport security**: In production, expose Mailpit and Laravel endpoints over HTTPS/TLS; secure webhook traffic with HTTPS plus a shared secret or HMAC signature; enable STARTTLS on SMTP if required. Local development may run over plain HTTP on localhost.
- **Auditing**: Track who views, downloads, or marks a message processed (timestamp + user ID) to maintain an audit trail.
- **Regulations**: No special mandates yet; keep data within your deployment region, adhere to the 7-day retention policy, and document internal-use-only handling of captured messages.

## Lifecycle Scenarios
- **Local Development**: Run Mailpit locally (`./mailpit.exe --smtp "127.0.0.1:1025" --listen "127.0.0.1:8025"`). Laravel connects via localhost. Developers open the Mailbox UI inside AssetManagement to verify emails.
- **Testing/QA**: CI or QA environments launch Mailpit (binary or automated service). Tests can hit a seeded webhook endpoint or fetch messages via API to assert email content.
- **Production Support**: Deploy Mailpit as a companion service next to Laravel Cloud. Staff use the same Mailbox UI to audit password resets, with TLS, authentication, and retention policies enabled.

## Deployment Strategy (Phase 0 Decision)
- **Local Windows**: use the downloaded binary at `C:\tools\Mailpit`. Launch with `./mailpit.exe --smtp "127.0.0.1:1025" --listen "127.0.0.1:8025"` from Git Bash or PowerShell, or run the helper script `tools/mailpit/start-mailpit.ps1` which auto-detects the binary path (set `MAILPIT_EXE` if installed elsewhere).
- **Local macOS**: install via Homebrew (`brew install mailpit`) or download the darwin binary. Start with `mailpit --smtp 127.0.0.1:1025 --listen 127.0.0.1:8025`, or execute `tools/mailpit/start-mailpit.sh` after making it executable (`chmod +x`). Teams can add an npm/yarn script to run both Laravel and Mailpit together.
- **Testing/CI**: bundle the Mailpit binary in the build artifacts or download it during CI setup. Run Mailpit as a background job before executing Laravel feature tests so password-reset assertions can hit the webhook endpoint.
- **Production**: provision a lightweight Ubuntu VM (1 vCPU, 1GB RAM) in the same private network as the Laravel Cloud deployment. Install Mailpit as a systemd service, bind SMTP to an internal IP (e.g., `10.x.x.x:1025`), expose the HTTP API behind HTTPS via Nginx, and store the persistent database under `/var/lib/mailpit`. If Laravel Cloud adds sidecar containers later, we can migrate with the same service configuration.

## Integration Method (Phase 0 Decision)
- Use Mailpit's webhook mechanism to push newly captured emails to AssetManagement. Start Mailpit with `--webhook-url https://mailbox.asset-app.local/api/mailpit/inbound --webhook-limit 5 --label assetmanagement`.
- Secure the webhook with the `X-Mailpit-Token` header (Mailpit flag `--ui-auth-file` or envoy config) and verify the signature plus request IP inside Laravel before processing.
- Expected payload (trimmed example):
  ```json
  {
    "id": "01HF07K2PR7E9Z7K6CJ9B6D6YT",
    "created": "2025-10-23T18:12:44.141Z",
    "subject": "Password Reset Request",
    "from": {"address": "noreply@asset-app.test", "name": "Asset Management"},
    "to": [{"address": "user@example.com", "name": "User Name"}],
    "text": "You requested a password reset...",
    "html": "<p>You requested a password reset...</p>",
    "attachments": [],
    "size": 3214,
    "tags": ["assetmanagement", "reset"]
  }
  ```
- Laravel responds with HTTP 204 after queuing an ingest job. On failure, Mailpit retries according to its default backoff; we can add a manual fallback command (`php artisan mailpit:sync`) that fetches `GET /api/v1/messages` if recovery is needed.

## Mailbox UX Scope (Phase 0 Decision)
- **Inbox landing**: paginated list with subject, sender, recipient, received time, status badge (new/processed/expired), environment tag.
- **Filters/search**: quick filters for status, environment, recipient email, date range, and free-text search across subject/body.
- **Message detail**: HTML/plain rendering tabs, header metadata, attachment previews/downloads, raw source download, and action buttons (mark processed, copy reset link).
- **Reset workflow**: embedded guidance with "Open reset link" button, ability to mark step completed once the user confirms, optional note entry for audit.
- **Audit trail**: side panel showing who viewed/downloaded/marked the message, with timestamps and optional comments.
- **Settings/help**: documentation link, manual purge button (with confirmation), and status indicators for webhook health.

## Next Steps
- Move into Phase 1 tasks (tooling/scripts, `.env` template updates, production deployment notes) using the decisions above as the baseline.
- Continue documenting commands, environment variables, and operational runbooks as implementation progresses.

## Environment Configuration (Phase 1 Updates)
- `.env.example` now includes the baseline keys `MAILPIT_HOST`, `MAILPIT_SMTP_PORT`, `MAILPIT_HTTP_URL`, `MAILPIT_WEBHOOK_URL`, `MAILPIT_WEBHOOK_TOKEN`, `MAILBOX_STORAGE_DISK`, `MAILBOX_RETENTION_DAYS`, `MAILBOX_WEBHOOK_SIGNATURE_KEY`, and `MAILBOX_QUEUE`.
- Developers copy these values into `.env` and adjust per environment; production secrets (webhook token/signature key) live in the Laravel Cloud configuration store.
- The Mailbox module will read these keys to connect to Mailpit, validate webhook calls, and manage storage/retention policies.

## Local Run Workflow
1. From the project root, run `powershell -ExecutionPolicy Bypass -File tools/mailpit/start-mailpit.ps1` (Windows) or `./tools/mailpit/start-mailpit.sh` (macOS/Linux) to start Mailpit.
2. In a second terminal, launch the Laravel app with `php artisan serve` (or your preferred local server) and ensure `.env` points `MAIL_HOST`/`MAIL_PORT` to Mailpit.
3. Optionally start Vite with `npm run dev` for the front-end experience.
4. Visit `http://127.0.0.1:8025` for the Mailpit native UI or the upcoming in-app Mailbox page (`/mailbox`) to inspect captured emails.
5. Run `php artisan queue:work --queue=${MAILBOX_QUEUE}` to process ingestion and attachment jobs, and execute `php artisan mailpit:sync` when you need to backfill messages from the Mailpit API.

## Production Deployment Plan (Phase 1 Updates)
- **Compute**: provision a lightweight Ubuntu 22.04 VM (1 vCPU / 1 GB RAM) inside the same VPC or private network as Laravel Cloud. Assign an internal hostname such as `mailpit.internal.asset-app`.
- **Installation**: create a dedicated system user (`mailpit`), download the latest Linux amd64 binary, place it under `/opt/mailpit`, and configure permissions so only that user can execute it.
- **Service management**: install a `systemd` unit (`/etc/systemd/system/mailpit.service`) that launches Mailpit with required flags (`--smtp 10.x.x.x:1025 --listen 10.x.x.x:8025 --webhook-url https://mailbox.asset-app.com/mailpit/webhook --webhook-limit 5 --label assetmanagement`). Enable and start the service so it auto-restarts on failure.
- **Storage**: set `--database /var/lib/mailpit/mailpit.db` and place attachments under `/var/lib/mailpit/attachments`. Back up this directory using your existing snapshot/backup tooling with a 7-day retention policy.
- **Networking**: expose SMTP port 1025 only to the Laravel application security group; expose HTTP port 8025 through an internal load balancer or reverse proxy (Nginx) that terminates TLS and enforces basic auth. Block public ingress.
- **Secrets**: inject webhook tokens and credentials via environment variables or a secrets manager, not in plaintext service files. Rotate them alongside Laravel Cloud secrets.
- **Failover**: document how to redeploy the service quickly (Ansible/Terraform script or VM image) and test restart scenarios (`systemctl restart mailpit`) during staging rehearsals.

## Monitoring & Logging (Phase 1 Updates)
- Enable structured logging by starting Mailpit with `--log-file /var/log/mailpit/service.log` and forward logs to your central logging stack (e.g., CloudWatch, ELK) with filebeat/fluent-bit.
- Add a `systemd` health check using `mailpit readyz --address http://127.0.0.1:8025` as an ExecStartPost or cron job; alert if the command fails.
- Turn on Prometheus metrics with `--enable-prometheus 0.0.0.0:9090` and scrape the metrics endpoint from your monitoring platform to track message counts, webhook failures, and queue depth.
- Configure uptime checks for the webhook endpoint (`/mailpit/webhook`) and the internal web UI to ensure TLS certificates and authentication stay valid.
- Capture key application metrics inside Laravel (ingest job successes/failures, mailbox processing times) using your existing logging/monitoring approach so Mailpit and Laravel insights can be correlated.

# Email Notifications Setup

This guide documents how to enable and verify email flows (password reset, alerts) in AssetManagement.

## 1. Environment Configuration
- Default mailer: `MAIL_MAILER=smtp` (use `log` or `array` locally when no SMTP is available).
- Required `.env` keys:
  ```ini
  MAIL_MAILER=smtp
  MAIL_HOST=
  MAIL_PORT=
  MAIL_USERNAME=
  MAIL_PASSWORD=
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS="noreply@asset-app.local"
  MAIL_FROM_NAME="Asset Management"
  ```
- For local testing without credentials, set `MAIL_MAILER=log` and tail `storage/logs/laravel.log`, or run Mailpit (`mailpit --smtp 1025 --http 8025`) and point `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`.

## 2. Local Mailpit Setup
- Launch Mailpit using the helper script shipped with the repo:
  ```powershell
  # Windows
  powershell -ExecutionPolicy Bypass -File tools/mailpit/start-mailpit.ps1
  ```
  ```bash
  # macOS/Linux
  ./tools/mailpit/start-mailpit.sh
  ```
- Point the AssetManagement `.env` SMTP settings to Mailpit (`MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`, `MAIL_MAILER=smtp`).
- Visit `http://127.0.0.1:8025` to inspect captured messages via the Mailpit UI; the upcoming in-app Mailbox module will surface the same data inside Laravel.

## 3. Password Reset Flow
- Laravel Fortify already handles the reset process.
- Trigger via the login screen modal (“Forgot password?”) or by visiting `/forgot-password`, entering the user email, then checking the configured mail transport.
- Automated coverage lives in `tests/Feature/Auth/PasswordResetTest.php`; run `php artisan test --testsuite=Feature --filter=PasswordResetTest` after changing mail setup.

## 4. Application Notifications
- Custom notifications live in `app/Notifications`. Extend `Illuminate\Notifications\Notification` and implement `via()` to include `mail` when needed.
- Example skeleton:
  ```php
  class AssetAlertNotification extends Notification
  {
      public function via($notifiable)
      {
          return ['mail', 'database'];
      }

      public function toMail($notifiable)
      {
          return (new MailMessage)
              ->subject('Asset Alert')
              ->line('An asset requires attention.')
              ->action('View Asset', url('/assets'));
      }
  }
  ```
- Dispatch with `$user->notify(new AssetAlertNotification($payload));` or `Notification::send()` for bulk recipients.

## 5. Testing & QA Checklist
- [ ] Update `.env` with the desired mail transport and run `php artisan config:cache`.
- [ ] Execute a password reset manually and confirm the message arrives (Mailpit/log/real SMTP).
- [ ] If introducing new mail notifications, add/extend Feature tests to fake the notification channel (`Notification::fake()`) and assert `Notification::assertSentTo()`.
- [ ] For production, verify credentials with `php artisan tinker`:
  ```php
  Notification::route('mail', 'test@example.com')->notify(new AssetAlertNotification());
  ```

## 6. Production Readiness
- Rotate SMTP credentials via secrets management; never commit them.
- Monitor failed jobs if notifications are queued (`QUEUE_CONNECTION=database` + Horizon preferred).
- Document any external provider requirements (Mailgun/Postmark) in deployment runbooks.

## 7. Mailpit-Based Mailbox Module (Current Plan)
- **Architecture**: keep Mailpit as the SMTP listener/ingestor and build a Mailbox module inside AssetManagement that provides the Gmail-lite inbox UI, storage, search, and audit history. No second Laravel app is required.
- **Local workflow**: run Mailpit (`mailpit --listen 127.0.0.1:1025 --http 127.0.0.1:8025 --webhook http://127.0.0.1:8000/mailpit/webhook`) alongside the main Laravel server. Mailpit captures outgoing reset emails and either exposes them via its UI or forwards them to the Mailbox webhook where they are persisted.
- **Hosting**: deploy AssetManagement (with the Mailbox module) on Laravel Cloud; provision Mailpit as a sidecar container/VM inside the same private network. Keep SMTP/HTTP endpoints private, protect the Mailpit UI with auth, and secure webhook traffic with shared secrets or signed payloads.
- **Environment variables**: continue using a single `.env` in AssetManagement. Add keys such as `MAILPIT_HOST`, `MAILPIT_PORT`, `MAILPIT_API_URL`, `MAILPIT_WEBHOOK_SECRET`, `MAILBOX_STORAGE_DISK`, and `MAILBOX_RETENTION_DAYS`.
- **Security**: bind Mailpit ports to localhost (dev) or internal IPs (cloud), enforce basic auth on the Mailpit UI, validate webhook signatures, store attachments on restricted disks/buckets, and apply retention policies to captured messages.
- **Reset flow**: staff initiates "Forgot password" -> Laravel sends the reset email to Mailpit -> Mailbox module ingests and displays the message -> clicking the reset link opens the standard Laravel reset form (optionally extended with SMS/backup checks) -> after success the user returns to the login screen and the message can be marked as processed.

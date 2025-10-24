# Mailbox Module Maintenance

This document outlines procedures for rolling back, cleaning up, and managing the Mailbox module.

## Rollback Procedures

To quickly disable the Mailbox module in case of critical issues:

1.  **Disable Routes**: In `routes/web.php`, comment out or remove the routes related to the `MailboxController` and the `MailpitWebhookController`.
2.  **Disable Scheduled Jobs**: If you are using `mailpit:sync` or `mailbox:purge-expired` via the scheduler, comment them out in `app/Console/Kernel.php`.
3.  **Revert Mail Driver (Optional)**: To restore email sending via another service, update your `.env` file to point `MAIL_MAILER`, `MAIL_HOST`, etc., to the desired service instead of Mailpit.

To completely remove the module's data (destructive):

1.  **Drop Tables**: Run the `down()` method of the mailbox migrations or manually drop the tables: `mailbox_notes`, `mailbox_events`, `mailbox_attachments`, `mailbox_recipients`, `mailbox_messages`.
2.  **Remove Config**: Delete `config/mailbox.php`.
3.  **Remove Code**: Delete the relevant controllers, models, jobs, commands, and Vue components associated with the mailbox.

## Cleanup Procedures

### Purging All Messages

A command is provided to purge all data from the mailbox. This includes all database records and any downloaded attachments from storage. This is useful for cleaning up a development or testing environment.

```bash
php artisan mailbox:purge-all
```
The command will ask for confirmation before deleting data.

### Purging Expired Messages

A scheduled job, `App\Jobs\Mailbox\PurgeExpiredMailboxMessages`, runs periodically to delete messages older than the retention period defined in `config('mailbox.retention_days')`. You can run this manually if needed:

```bash
# From tinker
dispatch(new \App\Jobs\Mailbox\PurgeExpiredMailboxMessages());
```

### Regenerating Secrets

The Mailbox module uses a webhook token to secure the ingestion endpoint.

-   `MAILPIT_WEBHOOK_TOKEN`: A shared secret between Mailpit and AssetManagement.

To regenerate:

1.  Generate a new secure, random string.
2.  Update the `MAILPIT_WEBHOOK_TOKEN` value in your `.env` file (and in your production environment's secret manager).
3.  Update the token in the configuration of your Mailpit instance.

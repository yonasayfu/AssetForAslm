<?php

return [
    'mailpit_host' => env('MAILPIT_HOST', '127.0.0.1'),
    'mailpit_smtp_port' => env('MAILPIT_SMTP_PORT', 1025),
    'mailpit_http_url' => env('MAILPIT_HTTP_URL', 'http://127.0.0.1:8025'),
    'mailpit_webhook_url' => env('MAILPIT_WEBHOOK_URL', 'http://127.0.0.1:8000/mailpit/webhook'),
    'webhook_token' => env('MAILPIT_WEBHOOK_TOKEN'),
    'webhook_signature_key' => env('MAILBOX_WEBHOOK_SIGNATURE_KEY'),
    'storage_disk' => env('MAILBOX_STORAGE_DISK', 'local'),
    'retention_days' => (int) env('MAILBOX_RETENTION_DAYS', 7),
    'queue' => env('MAILBOX_QUEUE', 'default'),
];

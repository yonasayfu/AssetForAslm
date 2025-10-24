# Manual Testing Guide: MFA Password Reset Flow

This guide outlines the steps to manually test the end-to-end MFA-based password reset flow.

### Prerequisites

1.  **Test User Setup**: Ensure you have a test user in your database with a `recovery_email` set. You can update an existing user's `recovery_email` directly in the database or via a seeder.
    *   Example: `php artisan tinker` -> `App\Models\User::first()->update(['recovery_email' => 'your_personal_email@example.com']);` (or use an email that Mailpit will capture).

### 1. Start Services

You will need three separate terminals running concurrently:

-   **Terminal 1 (Mailpit)**: Start the Mailpit SMTP server.
    ```bash
    # From the C:\tools\Mailpit directory, or wherever you placed it
    ./mailpit.exe --smtp "127.0.0.1:1025" --listen "127.0.0.1:8025"
    ```

-   **Terminal 2 (Laravel App)**: Start the Laravel development server.
    ```bash
    # From the project root
    php artisan serve
    ```

-   **Terminal 3 (Queue Worker)**: Start the queue worker to process email sending jobs.
    ```bash
    # From the project root
    php artisan queue:work
    ```

### 2. Configure `.env`

Ensure your `.env` file in the project root is configured to send emails to Mailpit for local testing:

```ini
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### 3. Trigger Password Reset (MFA Flow)

1.  In your web browser, navigate to your application's "Forgot Password" page (e.g., `http://127.0.0.1:8000/forgot-password`).
2.  Enter the **login email** of your test user (the one with the `recovery_email` set).
3.  Submit the form.

### 4. Verify Verification Code Sent

1.  **Check Mailpit**: The email containing the 6-digit verification code will be intercepted by Mailpit. You can see it in Mailpit's native web UI at `http://127.0.0.1:8025`.
2.  **Check Queue**: The queue worker terminal should show that it has processed the email sending job.
3.  **Retrieve Code**: Note down the 6-digit code from the email in Mailpit.

### 5. Enter Verification Code

1.  After submitting the "Forgot Password" form, your browser should have been redirected to the "Enter Code" page (e.g., `http://127.0.0.1:8000/enter-code`).
2.  Enter the 6-digit verification code you retrieved from Mailpit into the input field.
3.  Submit the form.

### 6. Reset Password

1.  Upon successful code verification, you will be redirected to the standard "Reset Password" page (e.g., `http://127.0.0.1:8000/reset-password/{token}`).
2.  Enter your new password and confirm it.
3.  Submit the form. You should then be redirected to the login page with a success message.

### 7. Test Helper Commands (Optional)

1.  In a new terminal, you can still use the `mailbox:show-latest` command to quickly retrieve the latest email (which should be your verification code email) and its content.
    ```bash
    php artisan mailbox:show-latest
    ```
2.  To clean up your test data, run the `mailbox:purge-all` command. Confirm the action when prompted.
    ```bash
    php artisan mailbox:purge-all
    ```
# Local MFA Testing Guide

This guide provides a clear, step-by-step workflow for testing the MFA-based password reset locally using Mailpit.

### Prerequisites

1.  **Test User Setup**: Ensure you have a test user in your database with a `recovery_email` set. This `recovery_email` should be an address that Mailpit will capture (e.g., `test@example.com`).
    *   Example: `php artisan tinker` -> `App\Models\User::first()->update(['recovery_email' => 'test@example.com']);`

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

Ensure your local `.env` file in the project root is configured to send emails to Mailpit:

```ini
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### 3. Initiate Password Reset

1.  In your web browser, navigate to your application's "Forgot Password" page (e.g., `http://127.0.0.1:8000/forgot-password`).
2.  Enter the **login email** of your test user (e.g., `admin@example.com`).
3.  Submit the form.

### 4. Retrieve Verification Code from Mailpit

1.  Your browser will redirect to the "Enter Code" page. **Do not enter a code yet.**
2.  Open Mailpit's web UI in your browser: `http://127.0.0.1:8025`.
3.  You will see a new email with the subject "Your Password Reset Verification Code". Open it.
4.  **Note down the 6-digit code** displayed in the email body.

### 5. Enter Verification Code

1.  Go back to the "Enter Code" page in your browser.
2.  Enter the 6-digit verification code you retrieved from Mailpit into the input field.
3.  Submit the form.

### 6. Reset Password

1.  Upon successful code verification, you will be redirected to the standard "Reset Password" page (e.g., `http://127.0.0.1:8000/reset-password/{token}`).
2.  Enter your new password and confirm it.
3.  Submit the form. You should then be redirected to the login page with a success message.

### 7. Clean Up (Optional)

1.  To clean up your test data, run the `mailbox:purge-all` command. Confirm the action when prompted.
    ```bash
    php artisan mailbox:purge-all
    ```

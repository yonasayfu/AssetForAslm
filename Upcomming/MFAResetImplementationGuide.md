# MFA-Based Password Reset: Implementation Guide

This document outlines the strategy for a secure, self-service password reset flow that is easy to manage in both development and production.

## 1. The Goal & The Professional Workflow

Our goal is to allow any staff member to securely reset their own password, even if their primary login email is not a real inbox. The standard, professional solution for this is **Multi-Factor Authentication (MFA)**.

Here is the user's journey:

1.  **Request Reset**: The user enters their login email (e.g., `staff@asset-app` or `staff@personal-email.com`) on the "Forgot Password" page.
2.  **Verification Step**: The application does **not** send a reset link. Instead, it sends a 6-digit verification code to the user's pre-registered **recovery contact method** (their personal email address).
3.  **Enter Code**: The user is redirected to a page asking them to enter the code they just received.
4.  **Reset Password**: Once the correct code is entered, the system trusts their identity and redirects them to the final page where they can set a new password.

This process is secure because it requires the user to prove they own a separate, verified account (their personal email).

## 2. Handling Your Requirements

### Login Email vs. Recovery Email

To make this work, we will distinguish between two email addresses for each user:

*   **Login Email**: The primary email they use to log into AssetManagement. This can be an app-specific address (`user@asset-app`) or a real one (`user@gmail.com`).
*   **Recovery Email**: A **guaranteed real, personal email address** (like a Gmail or Outlook account) that we will use *only* for sending security codes.

**Our Strategy**: For simplicity and maximum security, we will **use the MFA code-based flow for all users.** This avoids confusion and ensures every account has the same high level of protection.

### Minimizing Third-Party Services & Cost

You want to avoid complex, expensive third-party services. Here is how we will achieve that:

*   **No SMS/Phone Requirement**: Sending SMS messages reliably requires services like Twilio, which have costs and complexity. We will **not** implement SMS resets for now. We will rely exclusively on the user's personal recovery email.

*   **Simplified Production Emails**: To send the verification code to a user's personal email in production, we need a reliable email sending service. Trying to send email directly from the server is unreliable and often lands in spam.

    However, managing this is extremely simple. We will use a **Transactional Email Service**. Many top-tier providers offer **generous free plans** that will likely cover all your needs for a long time.

    **Recommended Production Setup:**
    1.  Sign up for a service like **Postmark** or **SendGrid** (their free tiers are often sufficient for thousands of emails per month).
    2.  They will give you an API Key.
    3.  On Laravel Cloud, you will add this API Key to your environment variables.

    That's it. There is no complex integration work. Laravel handles everything else. This is the standard, professional, and most cost-effective way to ensure your security codes are delivered instantly and reliably.

## 3. Development vs. Production Scenarios

This new workflow is easy to manage in both environments.

### Development Environment (Your Local Machine)

*   **Goal**: Test the reset flow quickly without needing a real email account.
*   **How it Works**: We will configure your local `.env` file to use **Mailpit** as the email driver.
*   **Scenario**: You click "Forgot Password" for a test user. Laravel will generate a verification code email. Instead of going to the internet, this email is **captured by Mailpit**. You can then open the Mailpit UI (`http://127.0.0.1:8025`), instantly see the code, and use it to complete the test. The `/mailbox` UI we built also serves as a great viewer here.

### Production Environment (Laravel Cloud)

*   **Goal**: Securely send the verification code to the real user.
*   **How it Works**: We will configure the `.env` file on Laravel Cloud to use the transactional email service (e.g., Postmark).
*   **Scenario**: A real staff member clicks "Forgot Password". Laravel sends the verification code to their registered **recovery email**. They check their personal inbox, get the code, and complete the reset. The system is secure and self-service.

## New Action Plan

If you approve this strategy, here is how we will proceed:

1.  **Database Change**: Add a `recovery_email` column to the `users` table.
2.  **Build the Logic**: Implement the new MFA flow (generating codes, sending them, and verifying them).
3.  **Create the UI**: Build the new "Enter Code" page.

This plan directly addresses your goals, providing a professional, secure, and low-maintenance solution. Please let me know if you approve so we can begin.
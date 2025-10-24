<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorEmailRecoveryCodeMailable extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public array $recoveryCodes;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, array $recoveryCodes)
    {
        $this->user = $user;
        $this->recoveryCodes = $recoveryCodes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Two-Factor Authentication Recovery Codes',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.two-factor-email-recovery-code',
            with: [
                'user' => $this->user,
                'recoveryCodes' => $this->recoveryCodes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

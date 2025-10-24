<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingUserRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $pendingUser)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('A new user is awaiting approval')
            ->greeting('Hello '.$notifiable->name.',')
            ->line("{$this->pendingUser->name} ({$this->pendingUser->email}) just created an account.")
            ->line('They are currently marked as an external user and require review before they can access internal resources.')
            ->action('Review user', route('users.edit', $this->pendingUser))
            ->line('Assign a staff profile and update their status to activate access.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user.pending',
            'user_id' => $this->pendingUser->id,
            'name' => $this->pendingUser->name,
            'email' => $this->pendingUser->email,
            'message' => "{$this->pendingUser->name} registered and is awaiting staff assignment.",
            'url' => route('users.edit', $this->pendingUser),
        ];
    }
}

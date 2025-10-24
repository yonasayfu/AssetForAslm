<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $assetName, public $assignedBy)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Asset Assignment: ' . $this->assetName)
                    ->line('You have been assigned a new asset: ' . $this->assetName . '.')
                    ->line('Assigned by: ' . $this->assignedBy)
                    ->action('View Asset', url('/assets/' . $this->assetName)) // Placeholder URL
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'asset_name' => $this->assetName,
            'assigned_by' => $this->assignedBy,
            'message' => 'You have been assigned a new asset: ' . $this->assetName . '.',
            'url' => url('/assets/' . $this->assetName),
        ];
    }
}


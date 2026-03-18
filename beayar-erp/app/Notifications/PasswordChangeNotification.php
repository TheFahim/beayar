<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangeNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Change Verification')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You requested to change your password for your Beayar ERP account.')
            ->line('Please click the button below to verify your identity and proceed with changing your password.')
            ->action('Verify & Change Password', route('tenant.profile.password.verify', ['token' => $this->token, 'email' => $notifiable->email]))
            ->line('This verification link will expire in 60 minutes.')
            ->line('If you did not request a password change, no further action is required.')
            ->salutation('Best regards,')
            ->subject('Beayar ERP Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
        ];
    }
}

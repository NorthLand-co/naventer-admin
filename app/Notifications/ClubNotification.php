<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kavenegar\Laravel\Message\KavenegarMessage;

class ClubNotification extends Notification
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Determine the delivery method based on availability of phone and email
        if ($notifiable->phone) {
            return ['kavenegar']; // Send SMS if phone is available
        } elseif ($notifiable->email) {
            return ['mail']; // Send Email if phone is not available but email is
        }

        return []; // No notification if neither is available
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Club Notification')
            ->line($this->message)
            ->action('View Club', url('/clubs'))
            ->line('Thank you for being a part of our club!');
    }

    public function toKavenegar($notifiable)
    {
        return (new KavenegarMessage($this->message))->from(env('KAVENEGAR_SENDER'));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
        ];
    }
}

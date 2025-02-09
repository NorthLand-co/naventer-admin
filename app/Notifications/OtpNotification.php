<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Kavenegar\Laravel\Message\KavenegarMessage;

class OtpNotification extends Notification
{
    use Queueable;

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        if (is_null($notifiable->phone)) {
            return ['mail'];
        }

        return ['kavenegar'];
    }

    public function toMail($notifiable)
    {
        dd('mail');
    }

    public function toKavenegar($notifiable)
    {
        return (new KavenegarMessage)->verifyLookup('otp', [$this->message]);
    }
}

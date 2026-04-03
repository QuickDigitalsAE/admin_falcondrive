<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendOtpNotification extends Notification
{
    use Queueable;

    protected $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
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
            ->subject('Your OTP Code')
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line('Your One-Time Password (OTP) is:')
            ->line("**{$this->otp}**")
            ->line('This OTP is valid for 5 minutes.')
            ->line('If you did not request this, please ignore this email.')
            ->salutation('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'otp' => $this->otp,
        ];
    }
}

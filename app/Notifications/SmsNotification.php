<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $message,
        private ?string $phoneNumber = null
    ) {}

    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms(object $notifiable): array
    {
        return [
            'to' => $this->phoneNumber ?? $notifiable->phone,
            'message' => $this->message,
        ];
    }
}

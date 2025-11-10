<?php

namespace App\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function __construct(
        private readonly SmsService $smsService
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $smsData = $notification->toSms($notifiable);

        if (! isset($smsData['to']) || ! isset($smsData['message'])) {
            Log::error('SMS notification missing required data', [
                'notifiable' => $notifiable::class,
                'notification' => $notification::class,
            ]);

            return;
        }

        $result = $this->smsService->sendSms($smsData['to'], $smsData['message']);

        if (! $result['success']) {
            Log::error('SMS notification failed', [
                'notifiable' => $notifiable::class,
                'to' => $smsData['to'],
                'error' => $result['message'],
            ]);
        }
    }
}

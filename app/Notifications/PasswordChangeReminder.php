<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PasswordChangeReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'password_reminder',
            'title' => __('notifications.password_reminder_title'),
            'message' => __('notifications.password_reminder_message'),
            'action_url' => '/admin/profile',
            'action_label' => __('notifications.change_password_action'),
            'priority' => 'medium',
        ];
    }
}

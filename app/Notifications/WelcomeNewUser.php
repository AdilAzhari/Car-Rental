<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNewUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
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
            ->subject(__('notifications.welcome_subject'))
            ->greeting(__('notifications.welcome_greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.welcome_message'))
            ->line(__('notifications.welcome_security_notice'))
            ->action(__('notifications.update_profile_action'), url('/admin/profile'))
            ->line(__('notifications.welcome_thank_you'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => __('notifications.welcome_title'),
            'message' => __('notifications.welcome_database_message'),
            'action_url' => '/admin/profile',
            'action_label' => __('notifications.update_profile_action'),
            'priority' => 'high',
        ];
    }
}

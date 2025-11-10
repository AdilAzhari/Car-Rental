<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    use Queueable;

    public function __construct(
        public User $user
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'New User Registered',
            'body' => "{$this->user->name} ({$this->user->email}) has registered as a {$this->user->role->value}",
            'icon' => 'heroicon-o-user-plus',
            'icon_color' => 'success',
            'user_id' => $this->user->id,
            'user_role' => $this->user->role->value,
            'actions' => [
                [
                    'label' => 'View User',
                    'url' => route('filament.admin.resources.users.edit', ['record' => $this->user->id]),
                ],
            ],
        ];
    }
}

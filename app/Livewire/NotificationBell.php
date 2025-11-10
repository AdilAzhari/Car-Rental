<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;

    public $notifications = [];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->notifications = $user->unreadNotifications()->limit(10)->get()->toArray();
            $this->unreadCount = $user->unreadNotifications()->count();
        }
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
            $this->loadNotifications();

            session()->flash('success', __('notifications.all_marked_as_read'));
        }
    }

    public function render(): View|Factory
    {
        return view('livewire.notification-bell');
    }
}

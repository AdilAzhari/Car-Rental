<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NewUserNotificationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users in admin panel
        if ($request->is('admin*') && auth()->check()) {
            $user = auth()->user();

            // Check if user is new and hasn't been shown welcome message yet
            if ($user->is_new_user) {
                // Show welcome notification
                Notification::make()
                    ->title(__('notifications.welcome_admin_title'))
                    ->body(__('notifications.welcome_admin_message', ['name' => $user->name]))
                    ->success()
                    ->persistent()
                    ->actions([
                        Action::make('update_profile')
                            ->label(__('notifications.update_profile_action'))
                            ->url('/admin/profile')
                            ->button(),
                        Action::make('dismiss')
                            ->label(__('notifications.dismiss'))
                            ->close()
                            ->color('gray'),
                    ])
                    ->send();

                // Check if they need to change password
                if (! $user->has_changed_default_password && $user->created_at->diffInDays(now()) >= 1) {
                    Notification::make()
                        ->title(__('notifications.password_reminder_title'))
                        ->body(__('notifications.password_reminder_admin_message'))
                        ->warning()
                        ->persistent()
                        ->actions([
                            Action::make('change_password')
                                ->label(__('notifications.change_password_action'))
                                ->url('/admin/profile')
                                ->button(),
                        ])
                        ->send();
                }

                // Mark user as no longer new (to avoid showing notification repeatedly)
                $user->update(['is_new_user' => false]);
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected function getColumns(): int
    {
        return 4;
    }

    #[\Override]
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && $user->role === UserRole::ADMIN;
    }

    #[\Override]
    protected function getStats(): array
    {
        // Total users
        $totalUsers = User::query()->count();

        // Users by role
        User::query()->where('role', UserRole::ADMIN)->count();
        $ownerCount = User::query()->where('role', UserRole::OWNER)->count();
        $renterCount = User::query()->where('role', UserRole::RENTER)->count();

        // Recent registrations (last 30 days)
        $recentRegistrations = User::query()->where('created_at', '>=', now()->subDays(30))->count();

        // Verified users
        $verifiedUsers = User::query()->whereNotNull('email_verified_at')->count();

        // Active users (users with bookings in last 30 days)
        $activeUsers = User::query()->whereHas('rentals', function ($query): void {
            $query->where('created_at', '>=', now()->subDays(30));
        })->count();

        // Registration trend for the last 7 days
        $registrationTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::query()->whereDate('created_at', $date)->count();
            $registrationTrend[] = $count;
        }

        return [
            Stat::make(__('widgets.total_users'), $totalUsers)
                ->description(__('widgets.all_registered_users'))
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->chart($registrationTrend),

            Stat::make(__('widgets.renters'), $renterCount)
                ->description(__('widgets.customer_accounts'))
                ->descriptionIcon('heroicon-o-user')
                ->color('info')
                ->chart(array_slice($registrationTrend, -4)),

            Stat::make(__('widgets.owners'), $ownerCount)
                ->description(__('widgets.vehicle_owners'))
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart(array_slice($registrationTrend, -4)),

            Stat::make(__('widgets.recent_signups'), $recentRegistrations)
                ->description(__('widgets.last_30_days'))
                ->descriptionIcon('heroicon-o-user-plus')
                ->color($recentRegistrations > 10 ? 'success' : 'warning')
                ->chart(array_slice($registrationTrend, -5)),

            Stat::make(__('widgets.verified'), $verifiedUsers)
                ->description(__('widgets.email_verified_users'))
                ->descriptionIcon('heroicon-o-shield-check')
                ->color('success')
                ->chart([
                    max(1, $verifiedUsers * 0.2),
                    max(1, $verifiedUsers * 0.4),
                    max(1, $verifiedUsers * 0.6),
                    max(1, $verifiedUsers * 0.8),
                    max(1, $verifiedUsers),
                ]),

            Stat::make(__('widgets.active_users'), $activeUsers)
                ->description(__('widgets.active_last_30_days'))
                ->descriptionIcon('heroicon-o-bolt')
                ->color('info')
                ->chart([
                    max(1, $activeUsers * 0.3),
                    max(1, $activeUsers * 0.6),
                    max(1, $activeUsers * 0.8),
                    max(1, $activeUsers),
                ]),
        ];
    }
}

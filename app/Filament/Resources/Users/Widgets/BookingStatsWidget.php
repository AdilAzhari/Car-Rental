<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends BaseWidget
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

        return $user && ($user->role === UserRole::ADMIN || $user->role === UserRole::OWNER);
    }

    #[\Override]
    protected function getStats(): array
    {
        $user = auth()->user();
        $ownerId = ($user && $user->role === UserRole::OWNER) ? $user->id : null;

        // Use our awesome model scopes! 🚀
        $totalBookings = Booking::when($ownerId, fn ($q) => $q->whereHas('vehicle', fn ($vq) => $vq->where('owner_id', $ownerId)))->count();

        $activeBookings = Booking::query()->active()
            ->when($ownerId, fn ($q) => $q->whereHas('vehicle', fn ($vq) => $vq->where('owner_id', $ownerId)))
            ->count();

        $upcomingBookings = Booking::query()->upcoming(7)
            ->when($ownerId, fn ($q) => $q->whereHas('vehicle', fn ($vq) => $vq->where('owner_id', $ownerId)))
            ->count();

        $overdueBookings = Booking::query()->overdue()
            ->when($ownerId, fn ($q) => $q->whereHas('vehicle', fn ($vq) => $vq->where('owner_id', $ownerId)))
            ->count();

        $needsAttention = Booking::query()->requiringAttention()
            ->when($ownerId, fn ($q) => $q->whereHas('vehicle', fn ($vq) => $vq->where('owner_id', $ownerId)))
            ->count();

        // Revenue using our scope
        $thisMonthRevenue = Booking::query()->revenueInPeriod(
            now()->startOfMonth(),
            now()->endOfMonth(),
            $ownerId
        )->sum('total_amount');

        // Booking trend for the last 7 days
        $bookingTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Booking::when($ownerId, fn ($q) => $q->whereHas('vehicle', fn ($vq) => $vq->where('owner_id', $ownerId)))
                ->whereDate('created_at', $date)
                ->count();
            $bookingTrend[] = $count;
        }

        return [
            Stat::make(__('widgets.total_bookings'), $totalBookings)
                ->description(__('widgets.all_time_bookings'))
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary')
                ->chart($bookingTrend),

            Stat::make(__('widgets.active_bookings'), $activeBookings)
                ->description(__('widgets.currently_ongoing'))
                ->descriptionIcon('heroicon-o-play-circle')
                ->color($activeBookings > 0 ? 'success' : 'gray')
                ->chart(array_slice($bookingTrend, -4)),

            Stat::make(__('widgets.upcoming_bookings'), $upcomingBookings)
                ->description(__('widgets.next_7_days'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color($upcomingBookings > 0 ? 'info' : 'gray')
                ->chart(array_slice($bookingTrend, -4)),

            Stat::make(__('widgets.overdue_returns'), $overdueBookings)
                ->description(__('widgets.vehicles_overdue'))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdueBookings > 0 ? 'danger' : 'success')
                ->chart(array_slice($bookingTrend, -3)),

            Stat::make(__('widgets.needs_attention'), $needsAttention)
                ->description(__('widgets.requiring_action'))
                ->descriptionIcon('heroicon-o-bell-alert')
                ->color($needsAttention > 0 ? 'warning' : 'success')
                ->chart(array_slice($bookingTrend, -3)),

            Stat::make(__('widgets.month_revenue'), 'RM '.number_format($thisMonthRevenue, 2))
                ->description(__('widgets.current_month_earnings'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart([
                    max(1, $thisMonthRevenue * 0.2),
                    max(1, $thisMonthRevenue * 0.4),
                    max(1, $thisMonthRevenue * 0.6),
                    max(1, $thisMonthRevenue * 0.8),
                    max(1, $thisMonthRevenue),
                ]),
        ];
    }
}

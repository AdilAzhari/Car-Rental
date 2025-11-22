<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttentionRequiredWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

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

        // Get bookings requiring attention using our scope! 🎯
        $attentionBookings = Booking::query()->requiringAttention()
            ->when($ownerId, fn ($q) => $q->whereHas('vehicle', fn ($vq) => $vq->where('owner_id', $ownerId)));

        $totalNeedsAttention = $attentionBookings->count();

        // Break down by type of attention needed
        $pendingPayments = (clone $attentionBookings)
            ->where('payment_status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->count();

        $overdueReturns = Booking::query()->overdue()
            ->when($ownerId, fn ($q) => $q->whereHas('vehicle', fn ($vq) => $vq->where('owner_id', $ownerId)))
            ->count();

        $missingReviews = (clone $attentionBookings)
            ->where('status', 'completed')
            ->where('end_date', '<', now()->subDays(7))
            ->doesntHave('review')
            ->count();

        return [
            Stat::make('🚨 Action Required', $totalNeedsAttention)
                ->description('Bookings needing your attention')
                ->descriptionIcon('heroicon-o-bell-alert')
                ->color($totalNeedsAttention > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.bookings.index', [
                    'activeTab' => 'attention',
                ])),

            Stat::make('💳 Pending Payments', $pendingPayments)
                ->description('Payments overdue 24+ hours')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color($pendingPayments > 0 ? 'warning' : 'success'),

            Stat::make('⏰ Overdue Returns', $overdueReturns)
                ->description('Vehicles not returned')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdueReturns > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.bookings.index', [
                    'activeTab' => 'overdue',
                ])),

            Stat::make('⭐ Missing Reviews', $missingReviews)
                ->description('Completed trips without reviews')
                ->descriptionIcon('heroicon-o-star')
                ->color($missingReviews > 5 ? 'warning' : 'info'),
        ];
    }
}

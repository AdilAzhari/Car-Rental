<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsWidget extends BaseWidget
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

        // Base query - filter by owner's vehicles if not admin
        $builder = Payment::query();
        if ($user && $user->role === UserRole::OWNER) {
            $builder->whereHas('booking.vehicle', function ($query) use ($user): void {
                $query->where('owner_id', $user->id);
            });
        }

        // Total payments
        $totalPayments = $builder->count();

        // Successful payments
        $successfulPayments = (clone $builder)->where('payment_status', PaymentStatus::CONFIRMED)->count();

        // Pending payments
        $pendingPayments = (clone $builder)->where('payment_status', PaymentStatus::PENDING)->count();

        // Failed payments
        (clone $builder)->where('payment_status', PaymentStatus::FAILED)->count();

        // Total revenue
        $totalRevenue = (clone $builder)
            ->where('payment_status', PaymentStatus::CONFIRMED)
            ->sum('amount');

        // This month's revenue
        $monthlyRevenue = (clone $builder)
            ->where('payment_status', PaymentStatus::CONFIRMED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Average payment amount
        $averagePayment = (clone $builder)
            ->where('payment_status', PaymentStatus::CONFIRMED)
            ->avg('amount') ?? 0;

        // Payment success rate
        $successRate = $totalPayments > 0 ? round(($successfulPayments / $totalPayments) * 100, 1) : 0;

        // Revenue trend for the last 7 days
        $revenueTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = (clone $builder)
                ->where('payment_status', PaymentStatus::CONFIRMED)
                ->whereDate('created_at', $date)
                ->sum('amount');
            $revenueTrend[] = $revenue > 0 ? $revenue : 1;
        }

        return [
            Stat::make(__('widgets.total_revenue'), 'RM '.number_format($totalRevenue, 2))
                ->description(__('widgets.all_time_revenue'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart($revenueTrend),

            Stat::make(__('widgets.monthly_revenue'), 'RM '.number_format($monthlyRevenue, 2))
                ->description(__('widgets.current_month'))
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info')
                ->chart(array_slice($revenueTrend, -5)),

            Stat::make(__('widgets.successful_payments'), $successfulPayments)
                ->description(__('widgets.completed_payments'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart(array_slice($revenueTrend, -4)),

            Stat::make(__('widgets.pending_payments'), $pendingPayments)
                ->description(__('widgets.awaiting_processing'))
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingPayments > 0 ? 'warning' : 'success')
                ->chart(array_slice($revenueTrend, -4)),

            Stat::make(__('widgets.payment_success_rate'), $successRate.'%')
                ->description(__('widgets.payment_success_rate'))
                ->descriptionIcon('heroicon-o-chart-pie')
                ->color($successRate >= 90 ? 'success' : ($successRate >= 75 ? 'warning' : 'danger'))
                ->chart([
                    max(1, $successRate * 0.2),
                    max(1, $successRate * 0.4),
                    max(1, $successRate * 0.6),
                    max(1, $successRate * 0.8),
                    max(1, $successRate),
                ]),

            Stat::make(__('widgets.average_payment'), 'RM '.number_format($averagePayment, 2))
                ->description(__('widgets.average_transaction'))
                ->descriptionIcon('heroicon-o-calculator')
                ->color('info')
                ->chart([
                    max(1, $averagePayment * 0.5),
                    max(1, $averagePayment * 0.7),
                    max(1, $averagePayment * 0.9),
                    max(1, $averagePayment),
                ]),
        ];
    }
}

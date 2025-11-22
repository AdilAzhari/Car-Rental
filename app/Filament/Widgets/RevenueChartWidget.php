<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    #[\Override]
    public function getHeading(): string
    {
        return __('widgets.revenue_chart');
    }

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    #[\Override]
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && ($user->role === UserRole::ADMIN || $user->role === UserRole::OWNER);
    }

    #[\Override]
    protected function getData(): array
    {
        $user = auth()->user();

        // Get the last 12 months
        $months = [];
        $revenues = [];
        $bookingsCount = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            // Base query for bookings
            $bookingQuery = Booking::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            // Filter by owner if not admin
            if ($user && $user->role === UserRole::OWNER) {
                $bookingQuery->whereHas('vehicle', function ($query) use ($user): void {
                    $query->where('owner_id', $user->id);
                });
            }

            $monthlyRevenue = (clone $bookingQuery)
                ->where('status', 'completed')
                ->sum('total_amount');

            $monthlyBookings = (clone $bookingQuery)->count();

            $revenues[] = (float) $monthlyRevenue;
            $bookingsCount[] = $monthlyBookings;
        }

        return [
            'datasets' => [
                [
                    'label' => __('widgets.revenue_currency'),
                    'data' => $revenues,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => __('widgets.bookings_count'),
                    'data' => $bookingsCount,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    #[\Override]
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => __('widgets.revenue_currency'),
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => __('widgets.bookings_count'),
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
        ];
    }
}

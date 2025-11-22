<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Helpers\CurrencyHelper;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\FilamentQueryOptimizationService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Override;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    #[Override]
    protected function getColumns(): int
    {
        // Responsive columns: 1 on mobile, 2 on tablet, 3 on desktop, 4 on large screens
        return match (true) {
            default => 4,
        };
    }

    protected function getResponsiveColumns(): array
    {
        return [
            'sm' => 1,    // Mobile: 1 column
            'md' => 2,    // Tablet: 2 columns
            'lg' => 3,    // Desktop: 3 columns
            'xl' => 4,    // Large screens: 4 columns
            '2xl' => 5,   // Extra large: 5 columns (for admin with 5 stats)
        ];
    }

    private function getCacheKey(string $prefix): string
    {
        $user = auth()->user();

        return "dashboard_stats_{$prefix}_{$user->role->value}_{$user->id}_".now()->format('Y-m-d-H');
    }

    private function getCachedData(string $key, callable $callback, int $ttl = 3600)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    #[Override]
    protected function getStats(): array
    {
        $user = auth()->user();
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Generate stats based on user role
        if ($user->role === UserRole::ADMIN) {
            return $this->getAdminStats($currentMonth, $lastMonth);
        } elseif ($user->role === UserRole::OWNER) {
            return $this->getOwnerStats($user, $currentMonth, $lastMonth);
        } else {
            return $this->getRenterStats();
        }
    }

    private function getAdminStats($currentMonth, $lastMonth): array
    {
        $filamentQueryOptimizationService = app(FilamentQueryOptimizationService::class);

        $stats = $this->getCachedData($this->getCacheKey('admin'), function () use ($currentMonth, $lastMonth, $filamentQueryOptimizationService): array {
            // Use the optimization service for dashboard stats
            $dashboardStats = $filamentQueryOptimizationService->getDashboardStats();

            // Additional time-based stats with optimized queries
            $baseCounts = DB::select("
                SELECT
                    'users' as type,
                    COUNT(*) as total,
                    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as current_month,
                    SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as last_month
                FROM car_rental_users
                UNION ALL
                SELECT
                    'vehicles',
                    COUNT(*),
                    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END),
                    SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END)
                FROM car_rental_vehicles
                UNION ALL
                SELECT
                    'bookings',
                    COUNT(*),
                    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END),
                    SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END)
                FROM car_rental_bookings
                UNION ALL
                SELECT
                    'reviews',
                    COUNT(*),
                    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END),
                    SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END)
                FROM car_rental_reviews
            ", [
                $currentMonth, $lastMonth, $currentMonth, // users
                $currentMonth, $lastMonth, $currentMonth, // vehicles
                $currentMonth, $lastMonth, $currentMonth, // bookings
                $currentMonth, $lastMonth, $currentMonth, // reviews
            ]);

            $counts = collect($baseCounts)->keyBy('type');

            // Merge with dashboard stats
            return [
                'users' => $counts->get('users'),
                'vehicles' => $counts->get('vehicles'),
                'bookings' => $counts->get('bookings'),
                'reviews' => $counts->get('reviews'),
                'dashboard_stats' => $dashboardStats,
                'vehicle_stats' => (object) [
                    'total' => $dashboardStats['total_vehicles'],
                    'published' => $dashboardStats['total_vehicles'],
                    'available' => $dashboardStats['total_vehicles'],
                ],
                'booking_stats' => (object) [
                    'active' => $dashboardStats['active_bookings'],
                    'completed' => 0,
                ],
                'revenue_stats' => (object) [
                    'total' => $dashboardStats['today_revenue'],
                    'current_month' => $dashboardStats['today_revenue'],
                    'last_month' => 0,
                ],
                'avg_rating' => $dashboardStats['avg_rating'],
            ];
        }, 900); // Cache for 15 minutes

        // Calculate growth percentages
        $userGrowth = $this->calculateGrowth($stats['users']->current_month, $stats['users']->last_month);
        $revenueGrowth = $this->calculateGrowth($stats['revenue_stats']->current_month, $stats['revenue_stats']->last_month);

        // Get chart data with caching
        $chartData = $this->getCachedData($this->getCacheKey('admin_charts'), fn (): array => $this->getOptimizedChartData(), 1800); // Cache for 30 minutes

        return [
            Stat::make(__('widgets.total_users'), number_format($stats['users']->total))
                ->description($userGrowth >= 0 ? "+{$userGrowth}% ".__('widgets.growth_from_last_month') : "{$userGrowth}% ".__('widgets.growth_from_last_month'))
                ->descriptionIcon($userGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userGrowth >= 0 ? 'success' : 'danger')
                ->chart($chartData['users']),

            Stat::make(__('widgets.available_vehicles'), number_format($stats['vehicle_stats']->available).' / '.number_format($stats['vehicle_stats']->published))
                ->description(__('widgets.ready_for_rent'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary')
                ->chart($chartData['vehicles']),

            Stat::make(__('widgets.active_bookings'), number_format($stats['booking_stats']->active))
                ->description($stats['bookings']->current_month.' '.__('widgets.bookings').' '.__('widgets.this_month'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info')
                ->chart($chartData['bookings']),

            Stat::make(__('widgets.monthly_revenue'), CurrencyHelper::format($stats['revenue_stats']->current_month))
                ->description($revenueGrowth >= 0 ? "+{$revenueGrowth}% ".__('widgets.growth_from_last_month') : "{$revenueGrowth}% ".__('widgets.growth_from_last_month'))
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'warning')
                ->chart($chartData['revenue']),

            Stat::make(__('resources.rating'), number_format($stats['avg_rating'], 1).'/5 ⭐')
                ->description($stats['reviews']->current_month.' '.__('resources.reviews').' '.__('widgets.this_month'))
                ->descriptionIcon('heroicon-m-star')
                ->color($stats['avg_rating'] >= 4 ? 'success' : ($stats['avg_rating'] >= 3 ? 'warning' : 'danger'))
                ->chart($chartData['reviews']),
        ];
    }

    private function calculateGrowth($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getOptimizedChartData(): array
    {
        $last7Days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

        // Single query for all chart data
        $chartData = DB::select("
            WITH RECURSIVE date_series AS (
                SELECT DATE(?) as date
                UNION ALL
                SELECT DATE_ADD(date, INTERVAL 1 DAY)
                FROM date_series
                WHERE date < DATE(?)
            )
            SELECT
                ds.date,
                COALESCE(u.count, 0) as users,
                COALESCE(v.count, 0) as vehicles,
                COALESCE(b.count, 0) as bookings,
                COALESCE(r.count, 0) as reviews,
                COALESCE(p.amount, 0) as revenue
            FROM date_series ds
            LEFT JOIN (
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM car_rental_users
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
            ) u ON ds.date = u.date
            LEFT JOIN (
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM car_rental_vehicles
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
            ) v ON ds.date = v.date
            LEFT JOIN (
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM car_rental_bookings
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
            ) b ON ds.date = b.date
            LEFT JOIN (
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM car_rental_reviews
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
            ) r ON ds.date = r.date
            LEFT JOIN (
                SELECT DATE(created_at) as date, SUM(amount) as amount
                FROM car_rental_payments
                WHERE payment_status = 'confirmed' AND DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
            ) p ON ds.date = p.date
            ORDER BY ds.date
        ", [
            $last7Days->first(), $last7Days->last(), // date series
            $last7Days->first(), $last7Days->last(), // users
            $last7Days->first(), $last7Days->last(), // vehicles
            $last7Days->first(), $last7Days->last(), // bookings
            $last7Days->first(), $last7Days->last(), // reviews
            $last7Days->first(), $last7Days->last(), // payments
        ]);

        return [
            'users' => array_column($chartData, 'users'),
            'vehicles' => array_column($chartData, 'vehicles'),
            'bookings' => array_column($chartData, 'bookings'),
            'reviews' => array_column($chartData, 'reviews'),
            'revenue' => array_column($chartData, 'revenue'),
        ];
    }

    private function getOwnerStats($user, $currentMonth, $lastMonth): array
    {
        $stats = $this->getCachedData($this->getCacheKey('owner'), function () use ($user, $currentMonth, $lastMonth): array {
            // Single optimized query for owner's vehicle stats
            $vehicleStats = DB::selectOne("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN status = 'published' AND is_available = 1 THEN 1 ELSE 0 END) as available
                FROM car_rental_vehicles
                WHERE owner_id = ?
            ", [$user->id]);

            // Booking stats for owner's vehicles
            $bookingStats = DB::selectOne("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN b.status = 'ongoing' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN b.created_at >= ? THEN 1 ELSE 0 END) as current_month
                FROM car_rental_bookings b
                INNER JOIN car_rental_vehicles v ON b.vehicle_id = v.id
                WHERE v.owner_id = ?
            ", [$currentMonth, $user->id]);

            // Revenue stats for owner's vehicles
            $revenueStats = DB::selectOne("
                SELECT
                    SUM(p.amount) as total,
                    SUM(CASE WHEN p.created_at >= ? THEN p.amount ELSE 0 END) as current_month,
                    SUM(CASE WHEN p.created_at >= ? AND p.created_at < ? THEN p.amount ELSE 0 END) as last_month
                FROM car_rental_payments p
                INNER JOIN car_rental_bookings b ON p.booking_id = b.id
                INNER JOIN car_rental_vehicles v ON b.vehicle_id = v.id
                WHERE v.owner_id = ? AND p.payment_status = 'confirmed'
            ", [$currentMonth, $lastMonth, $currentMonth, $user->id]);

            // Review stats for owner's vehicles
            $reviewStats = DB::selectOne('
                SELECT
                    COUNT(*) as total,
                    AVG(CASE WHEN r.is_visible = 1 THEN r.rating END) as average_rating,
                    SUM(CASE WHEN r.created_at >= ? THEN 1 ELSE 0 END) as current_month
                FROM car_rental_reviews r
                INNER JOIN car_rental_vehicles v ON r.vehicle_id = v.id
                WHERE v.owner_id = ?
            ', [$currentMonth, $user->id]);

            return [
                'vehicles' => $vehicleStats,
                'bookings' => $bookingStats ?: (object) ['total' => 0, 'active' => 0, 'completed' => 0, 'current_month' => 0],
                'revenue' => $revenueStats ?: (object) ['total' => 0, 'current_month' => 0, 'last_month' => 0],
                'reviews' => $reviewStats ?: (object) ['total' => 0, 'average_rating' => 0, 'current_month' => 0],
            ];
        }, 600); // Cache for 10 minutes

        $revenueGrowth = $this->calculateGrowth($stats['revenue']->current_month ?? 0, $stats['revenue']->last_month ?? 0);

        return [
            Stat::make(__('widgets.my_vehicles'), number_format($stats['vehicles']->total))
                ->description($stats['vehicles']->published.' '.__('widgets.published_comma_available').' '.$stats['vehicles']->available.' '.__('widgets.available_vehicles'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            Stat::make(__('widgets.total_bookings'), number_format($stats['bookings']->total))
                ->description($stats['bookings']->current_month.' '.__('widgets.bookings_this_month'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make(__('widgets.active_bookings'), number_format($stats['bookings']->active))
                ->description($stats['bookings']->completed.' '.__('widgets.completed_bookings'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('widgets.monthly_revenue'), CurrencyHelper::format($stats['revenue']->current_month ?? 0))
                ->description($revenueGrowth >= 0 ? "+{$revenueGrowth}% ".__('widgets.from_last_month') : "{$revenueGrowth}% ".__('widgets.from_last_month'))
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(__('widgets.average_rating'), number_format($stats['reviews']->average_rating ?: 0, 1).'/5 ⭐')
                ->description($stats['reviews']->total.' '.__('widgets.total_reviews').', '.$stats['reviews']->current_month.' '.__('widgets.this_month'))
                ->descriptionIcon('heroicon-m-star')
                ->color($stats['reviews']->average_rating >= 4 ? 'success' : ($stats['reviews']->average_rating >= 3 ? 'warning' : 'danger')),
        ];
    }

    private function getRenterStats(): array
    {
        $user = auth()->user();
        $currentMonth = now()->startOfMonth();

        $stats = $this->getCachedData($this->getCacheKey('renter'), function () use ($user, $currentMonth): array {
            // Get renter's booking stats
            $bookingStats = DB::selectOne("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as current_month
                FROM car_rental_bookings
                WHERE renter_id = ?
            ", [$currentMonth, $user->id]);

            // Get available vehicles count
            $availableVehicles = DB::selectOne("
                SELECT COUNT(*) as count
                FROM car_rental_vehicles
                WHERE status = 'published' AND is_available = 1
            ")->count;

            // Get spending stats
            $spendingStats = DB::selectOne("
                SELECT
                    SUM(p.amount) as total,
                    SUM(CASE WHEN p.created_at >= ? THEN p.amount ELSE 0 END) as current_month
                FROM car_rental_payments p
                INNER JOIN car_rental_bookings b ON p.booking_id = b.id
                WHERE b.renter_id = ? AND p.payment_status = 'confirmed'
            ", [$currentMonth, $user->id]);

            // Get review stats
            $reviewStats = DB::selectOne('
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as current_month
                FROM car_rental_reviews
                WHERE user_id = ?
            ', [$currentMonth, $user->id]);

            return [
                'bookings' => $bookingStats ?: (object) ['total' => 0, 'active' => 0, 'pending' => 0, 'completed' => 0, 'current_month' => 0],
                'available_vehicles' => $availableVehicles,
                'spending' => $spendingStats ?: (object) ['total' => 0, 'current_month' => 0],
                'reviews' => $reviewStats ?: (object) ['total' => 0, 'current_month' => 0],
            ];
        }, 600);

        return [
            Stat::make(__('widgets.my_bookings'), number_format($stats['bookings']->total))
                ->description($stats['bookings']->current_month.' '.__('widgets.bookings_this_month'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make(__('widgets.active_bookings'), number_format($stats['bookings']->active))
                ->description($stats['bookings']->pending.' '.__('widgets.pending').', '.$stats['bookings']->completed.' '.__('widgets.completed'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('widgets.available_vehicles'), number_format($stats['available_vehicles']))
                ->description(__('widgets.ready_for_booking'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),

            Stat::make(__('widgets.monthly_spending'), CurrencyHelper::format($stats['spending']->current_month ?? 0))
                ->description(CurrencyHelper::format($stats['spending']->total ?? 0).' '.__('widgets.total_spent'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make(__('widgets.my_reviews'), number_format($stats['reviews']->total))
                ->description($stats['reviews']->current_month.' '.__('widgets.reviews_this_month'))
                ->descriptionIcon('heroicon-m-star')
                ->color('info'),
        ];
    }
}

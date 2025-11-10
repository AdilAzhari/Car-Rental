<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardCacheService
{
    public const CACHE_TTL_SHORT = 300; // 5 minutes

    public const CACHE_TTL_MEDIUM = 900; // 15 minutes

    public const CACHE_TTL_LONG = 1800; // 30 minutes

    /**
     * Get cache key with user context and time-based invalidation
     */
    public function getCacheKey(string $prefix, ?int $userId = null): string
    {
        $userId ??= auth()->id();
        $userRole = auth()->user()?->role ?? 'guest';

        return "dashboard_{$prefix}_{$userRole}_{$userId}_".now()->format('Y-m-d-H');
    }

    /**
     * Remember cached data with automatic expiration
     */
    public function remember(string $key, callable $callback, int $ttl = self::CACHE_TTL_MEDIUM)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Clear all dashboard caches for a user
     */
    public function clearUserCache(?int $userId = null): void
    {
        $userId ??= auth()->id();
        $userRole = auth()->user()?->role ?? 'guest';
        $pattern = "dashboard_*_{$userRole}_{$userId}_*";

        // Clear cache entries matching the pattern
        /** @var \Illuminate\Redis\Connections\Connection $redis */
        $redis = Cache::store('redis')->getRedis();
        $keys = $redis->keys($pattern);
        if (! empty($keys)) {
            $redis->del($keys);
        }
    }

    /**
     * Get optimized admin statistics
     */
    public function getAdminStats($currentMonth, $lastMonth): array
    {
        return $this->remember(
            $this->getCacheKey('admin_stats'),
            function () use ($currentMonth, $lastMonth): array {
                // Single optimized query for all counts
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

                // Additional optimized queries
                $vehicleStats = DB::selectOne("
                    SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                        SUM(CASE WHEN status = 'published' AND is_available = 1 THEN 1 ELSE 0 END) as available
                    FROM car_rental_vehicles
                ");

                $bookingStats = DB::selectOne("
                    SELECT
                        SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                    FROM car_rental_bookings
                ");

                $revenueStats = DB::selectOne("
                    SELECT
                        SUM(amount) as total,
                        SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as current_month,
                        SUM(CASE WHEN created_at >= ? AND created_at < ? THEN amount ELSE 0 END) as last_month
                    FROM car_rental_payments
                    WHERE payment_status = 'confirmed'
                ", [$currentMonth, $lastMonth, $currentMonth]);

                $avgRating = DB::selectOne('
                    SELECT AVG(rating) as average
                    FROM car_rental_reviews
                    WHERE is_visible = 1
                ');

                return [
                    'users' => $counts->get('users'),
                    'vehicles' => $counts->get('vehicles'),
                    'bookings' => $counts->get('bookings'),
                    'reviews' => $counts->get('reviews'),
                    'vehicle_stats' => $vehicleStats,
                    'booking_stats' => $bookingStats,
                    'revenue_stats' => $revenueStats,
                    'avg_rating' => $avgRating->average ?? 0,
                ];
            },
            self::CACHE_TTL_MEDIUM
        );
    }

    /**
     * Get optimized chart data for the last 7 days
     */
    public function getChartData(): array
    {
        return $this->remember(
            $this->getCacheKey('chart_data'),
            function (): array {
                $last7Days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

                // Single optimized query for all chart data
                $chartData = DB::select("
                    WITH RECURSIVE date_series AS (
                        SELECT DATE(?) as date
                        UNION ALL
                        SELECT DATE(date, '+1 day')
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
            },
            self::CACHE_TTL_LONG
        );
    }

    /**
     * Get optimized owner statistics
     */
    public function getOwnerStats(int $userId, $currentMonth, $lastMonth): array
    {
        return $this->remember(
            $this->getCacheKey('owner_stats', $userId),
            function () use ($userId, $currentMonth, $lastMonth): array {
                // Single optimized query for owner's vehicle stats
                $vehicleStats = DB::selectOne("
                    SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                        SUM(CASE WHEN status = 'published' AND is_available = 1 THEN 1 ELSE 0 END) as available
                    FROM car_rental_vehicles
                    WHERE owner_id = ?
                ", [$userId]);

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
                ", [$currentMonth, $userId]);

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
                ", [$currentMonth, $lastMonth, $currentMonth, $userId]);

                // Review stats for owner's vehicles
                $reviewStats = DB::selectOne('
                    SELECT
                        COUNT(*) as total,
                        AVG(CASE WHEN r.is_visible = 1 THEN r.rating END) as average_rating,
                        SUM(CASE WHEN r.created_at >= ? THEN 1 ELSE 0 END) as current_month
                    FROM car_rental_reviews r
                    INNER JOIN car_rental_vehicles v ON r.vehicle_id = v.id
                    WHERE v.owner_id = ?
                ', [$currentMonth, $userId]);

                return [
                    'vehicles' => $vehicleStats,
                    'bookings' => $bookingStats ?: (object) ['total' => 0, 'active' => 0, 'completed' => 0, 'current_month' => 0],
                    'revenue' => $revenueStats ?: (object) ['total' => 0, 'current_month' => 0, 'last_month' => 0],
                    'reviews' => $reviewStats ?: (object) ['total' => 0, 'average_rating' => 0, 'current_month' => 0],
                ];
            },
            self::CACHE_TTL_SHORT
        );
    }

    /**
     * Get optimized renter statistics
     */
    public function getRenterStats(int $userId, $currentMonth): array
    {
        return $this->remember(
            $this->getCacheKey('renter_stats', $userId),
            function () use ($userId, $currentMonth): array {
                // Renter's booking stats
                $bookingStats = DB::selectOne("
                    SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as current_month
                    FROM car_rental_bookings
                    WHERE renter_id = ?
                ", [$currentMonth, $userId]);

                // Renter's spending stats
                $spendingStats = DB::selectOne("
                    SELECT
                        SUM(p.amount) as total,
                        SUM(CASE WHEN p.created_at >= ? THEN p.amount ELSE 0 END) as current_month
                    FROM car_rental_payments p
                    INNER JOIN car_rental_bookings b ON p.booking_id = b.id
                    WHERE b.renter_id = ? AND p.payment_status = 'confirmed'
                ", [$currentMonth, $userId]);

                // Renter's review stats
                $reviewStats = DB::selectOne('
                    SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as current_month
                    FROM car_rental_reviews
                    WHERE renter_id = ?
                ', [$currentMonth, $userId]);

                // Available vehicles for renting
                $availableVehicles = DB::selectOne("
                    SELECT COUNT(*) as total
                    FROM car_rental_vehicles
                    WHERE status = 'published' AND is_available = 1
                ");

                return [
                    'bookings' => $bookingStats ?: (object) ['total' => 0, 'active' => 0, 'completed' => 0, 'pending' => 0, 'current_month' => 0],
                    'spending' => $spendingStats ?: (object) ['total' => 0, 'current_month' => 0],
                    'reviews' => $reviewStats ?: (object) ['total' => 0, 'current_month' => 0],
                    'available_vehicles' => $availableVehicles->total ?? 0,
                ];
            },
            self::CACHE_TTL_SHORT
        );
    }

    /**
     * Calculate growth percentage between two values
     */
    public function calculateGrowth($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Warm up cache for all dashboard widgets
     */
    public function warmupCache(): void
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Warm up admin stats
        $this->getAdminStats($currentMonth, $lastMonth);

        // Warm up chart data
        $this->getChartData();

        // Note: Owner and renter stats are user-specific and will be warmed up on first access
    }
}

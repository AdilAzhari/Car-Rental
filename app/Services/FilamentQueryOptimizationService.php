<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilamentQueryOptimizationService
{
    /**
     * Get optimized booking query with eager loading
     */
    public function getOptimizedBookingQuery(): Builder
    {
        return Booking::query()
            ->select([
                'id', 'renter_id', 'vehicle_id', 'start_date', 'end_date',
                'total_amount', 'status', 'created_at', 'updated_at', 'payment_status', 'dropoff_location', 'pickup_location', 'total_amount',
                'insurance_fee', 'subtotal', 'days', 'daily_rate', 'tax_amount',
                'special_requests',
            ])
            ->with([
                'renter:id,name,email',
                'vehicle:id,owner_id,make,model,year,daily_rate,plate_number',
                'vehicle.owner:id,name',
            ]);
    }

    /**
     * Get optimized user query for admin tables
     */
    public function getOptimizedUserQuery(): Builder
    {
        return User::query()
            ->select([
                'id', 'name', 'email', 'role', 'status', 'email_verified_at',
                'created_at', 'updated_at', 'is_verified', 'last_login_at',
                'password_changed_at',
            ])
            ->withCount(['vehicles', 'bookings', 'reviews']);
    }

    /**
     * Get optimized vehicle query with relationships
     */
    public function getOptimizedVehicleQuery(): Builder
    {
        return Vehicle::query()
            ->select([
                'id', 'owner_id', 'make', 'model', 'year', 'daily_rate',
                'status', 'is_available', 'created_at', 'updated_at', 'plate_number', 'category',
                // Add fields needed for forms
                'color', 'fuel_type', 'transmission', 'seats', 'doors', 'engine_size', 'mileage',
                'location', 'pickup_location', 'insurance_included', 'insurance_expiry',
                'description', 'terms_and_conditions', 'featured_image', 'gallery_images',
                'documents', 'features', 'oil_type', 'last_oil_change', 'policy',
                'insurance_fine_amount', 'insurance_fine_paid', 'insurance_fine_paid_at',
                'insurance_fine_payment_method', 'insurance_fine_transaction_id',
                'traffic_violations', 'violations_last_checked', 'total_violations_count',
                'total_fines_amount', 'has_pending_violations',
                'parking_violations', 'parking_violations_last_checked',
                'total_parking_violations_count', 'total_parking_fines_amount',
                'has_pending_parking_violations', 'deleted_at',
            ])
            ->with([
                'owner:id,name,email',
                'images:id,vehicle_id,image_path,alt_text',
            ])
            ->withCount(['bookings', 'reviews']);
    }

    /**
     * Get optimized recent bookings query
     */
    public function getRecentBookingsQuery(int $limit = 10): Builder
    {
        return $this->getOptimizedBookingQuery()
            ->latest()
            ->limit($limit);
    }

    /**
     * Apply search optimization for large tables
     */
    public function applySearchOptimization(Builder $builder, string $searchTerm, array $searchableColumns): Builder
    {
        if ($searchTerm === '' || $searchTerm === '0') {
            return $builder;
        }

        return $builder->where(function ($q) use ($searchTerm, $searchableColumns): void {
            foreach ($searchableColumns as $searchableColumn) {
                if (str_contains($searchableColumn, '.')) {
                    // Relationship search
                    [$relation, $relationColumn] = explode('.', $searchableColumn, 2);
                    $q->orWhereHas($relation, function ($relationQuery) use ($relationColumn, $searchTerm): void {
                        $relationQuery->where($relationColumn, 'LIKE', "%{$searchTerm}%");
                    });
                } else {
                    // Direct column search
                    $q->orWhere($searchableColumn, 'LIKE', "%{$searchTerm}%");
                }
            }
        });
    }

    /**
     * Apply index hints for MySQL optimization
     */
    public function applyIndexHints(Builder $builder, string $table, array $indexes): Builder
    {
        if (config('database.default') === 'mysql' && $indexes !== []) {
            $indexHint = 'USE INDEX ('.implode(', ', $indexes).')';
            $builder->from(DB::raw("{$table} {$indexHint}"));
        }

        return $builder;
    }

    /**
     * Get optimized dashboard stats queries
     */
    public function getDashboardStats(): array
    {
        // Use raw queries for better performance
        $stats = DB::select("
            SELECT
                (SELECT COUNT(*) FROM car_rental_users) as total_users,
                (SELECT COUNT(*) FROM car_rental_vehicles WHERE status = 'published') as total_vehicles,
                (SELECT COUNT(*) FROM car_rental_bookings WHERE status IN ('confirmed', 'ongoing')) as active_bookings,
                (SELECT SUM(amount) FROM car_rental_payments WHERE payment_status = 'confirmed' AND created_at >= CURDATE()) as today_revenue,
                (SELECT AVG(rating) FROM car_rental_reviews WHERE is_visible = 1) as avg_rating
        ");

        return [
            'total_users' => $stats[0]->total_users ?? 0,
            'total_vehicles' => $stats[0]->total_vehicles ?? 0,
            'active_bookings' => $stats[0]->active_bookings ?? 0,
            'today_revenue' => $stats[0]->today_revenue ?? 0,
            'avg_rating' => round($stats[0]->avg_rating ?? 0, 1),
        ];
    }

    /**
     * Optimize paginated queries
     */
    public function optimizePagination(Builder $builder, int $perPage = 25): Builder
    {
        // Add ordering to ensure consistent pagination
        if (! $builder->getQuery()->orders) {
            $builder->orderBy('id', 'desc');
        }

        return $builder;
    }

    /**
     * Add performance monitoring to queries
     */
    public function monitorQueryPerformance(Builder $builder, string $context = 'unknown'): Builder
    {
        if (config('app.debug')) {
            $startTime = microtime(true);

            $builder->afterQuery(function () use ($startTime, $context): void {
                $executionTime = microtime(true) - $startTime;
                if ($executionTime > 0.1) { // Log slow queries (>100ms)
                    Log::warning('Slow query detected', [
                        'context' => $context,
                        'execution_time' => $executionTime,
                        'query' => request()->fullUrl(),
                    ]);
                }
            });
        }

        return $builder;
    }

    /**
     * Get bulk operation optimized query
     */
    public function getBulkOperationQuery(string $model, array $ids): Builder
    {
        $modelClass = "App\\Models\\{$model}";

        return $modelClass::query()
            ->whereIn('id', $ids)
            ->select('id'); // Only select needed columns for bulk operations
    }

    /**
     * Optimize count queries for large tables
     */
    public function getOptimizedCount(Builder $builder): int
    {
        // For very large tables, use approximate counts from INFORMATION_SCHEMA
        $tableName = $builder->getModel()->getTable();

        if ($this->shouldUseApproximateCount($tableName)) {
            $count = DB::selectOne('
                SELECT table_rows as approximate_count
                FROM information_schema.tables
                WHERE table_name = ? AND table_schema = DATABASE()
            ', [$tableName]);

            return $count->approximate_count ?? 0;
        }

        return $builder->count();
    }

    /**
     * Check if table is large enough to benefit from approximate counting
     */
    private function shouldUseApproximateCount(string $tableName): bool
    {
        // Use approximate count for tables with more than 100k rows
        $largeTableThreshold = 100000;

        $count = DB::selectOne('
            SELECT table_rows
            FROM information_schema.tables
            WHERE table_name = ? AND table_schema = DATABASE()
        ', [$tableName]);

        return ($count->table_rows ?? 0) > $largeTableThreshold;
    }

    /**
     * Get memory-efficient export query
     */
    public function getExportQuery(Builder $builder, int $chunkSize = 1000): \Generator
    {
        foreach ($builder->cursor() as $lazyCollection) {
            yield $lazyCollection;
        }
    }
}

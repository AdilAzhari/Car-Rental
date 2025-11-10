<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Enable query logging for performance monitoring
        if (config('app.debug')) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;

        // Log performance metrics for slow requests
        if ($executionTime > 2.0) { // Log requests taking more than 2 seconds
            $this->logSlowRequest($request, $executionTime, $memoryUsed);
        }

        // Add performance headers in debug mode
        if (config('app.debug')) {
            $response->headers->set('X-Execution-Time', round($executionTime * 1000, 2).'ms');
            $response->headers->set('X-Memory-Usage', $this->formatBytes($memoryUsed));

            if (DB::getQueryLog()) {
                $response->headers->set('X-Query-Count', count(DB::getQueryLog()));
            }
        }

        return $response;
    }

    /**
     * Log slow request details for performance analysis
     */
    private function logSlowRequest(Request $request, float $executionTime, int $memoryUsed): void
    {
        $queryLog = config('app.debug') ? DB::getQueryLog() : [];
        $slowQueries = array_filter($queryLog, fn (array $query): bool => $query['time'] > 100); // Queries over 100ms

        Log::warning('Slow request detected', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time' => round($executionTime, 3),
            'memory_used' => $this->formatBytes($memoryUsed),
            'query_count' => count($queryLog),
            'slow_query_count' => count($slowQueries),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Log slow queries separately for detailed analysis
        foreach ($slowQueries as $slowQuery) {
            Log::warning('Slow database query', [
                'query' => $slowQuery['query'],
                'bindings' => $slowQuery['bindings'],
                'time' => $slowQuery['time'].'ms',
                'url' => $request->fullUrl(),
            ]);
        }
    }

    /**
     * Format bytes into human-readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}

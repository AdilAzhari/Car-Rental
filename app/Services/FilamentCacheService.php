<?php

namespace App\Services;

use App\Filament\Widgets\DashboardStatsOverview;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class FilamentCacheService
{
    public static function getCacheKey(string $type, ?string $identifier = null): string
    {
        $key = "filament.$type";

        if ($identifier) {
            $key .= ".$identifier";
        }

        return $key;
    }

    public static function getWidgetData(string $widget, callable $callback, ?int $ttl = null): mixed
    {
        if (! config('filament.cache.widgets.enabled', true)) {
            return $callback();
        }

        $key = self::getCacheKey('widget', $widget);
        $ttl ??= config('filament.cache.widgets.ttl', 300);

        return Cache::store(config('filament.cache.widgets.store', 'default'))
            ->remember($key, $ttl, $callback);
    }

    public static function getResourceData(string $resource, callable $callback, ?int $ttl = null): mixed
    {
        if (! config('filament.cache.resources.enabled', true)) {
            return $callback();
        }

        $key = self::getCacheKey('resource', $resource);
        $ttl ??= config('filament.cache.resources.ttl', 3600);

        return Cache::remember($key, $ttl, $callback);
    }

    public static function getNavigationData(string $panel, callable $callback, ?int $ttl = null): mixed
    {
        if (! config('filament.cache.navigation.enabled', true)) {
            return $callback();
        }

        $key = self::getCacheKey('navigation', $panel);
        $ttl ??= config('filament.cache.navigation.ttl', 1800);

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function clearWidgetCache(?string $widget = null): void
    {
        if ($widget) {
            $key = self::getCacheKey('widget', $widget);
            Cache::forget($key);
        } else {
            // Clear all widget caches
            $store = Cache::store(config('filament.cache.widgets.store', 'default'));
            $keys = $store->get('filament.widget.keys', []);

            foreach ($keys as $key) {
                $store->forget($key);
            }

            $store->forget('filament.widget.keys');
        }
    }

    public static function clearResourceCache(?string $resource = null): void
    {
        if ($resource) {
            $key = self::getCacheKey('resource', $resource);
            Cache::forget($key);
        } else {
            // Clear all resource caches
            Cache::flush(); // Consider a more targeted approach in production
        }
    }

    public static function clearNavigationCache(?string $panel = null): void
    {
        if ($panel) {
            $key = self::getCacheKey('navigation', $panel);
            Cache::forget($key);
        } else {
            // Clear all navigation caches
            $keys = Cache::get('filament.navigation.keys', []);

            foreach ($keys as $key) {
                Cache::forget($key);
            }

            Cache::forget('filament.navigation.keys');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function clearAllCache(): void
    {
        self::clearWidgetCache();
        self::clearResourceCache();
        self::clearNavigationCache();
    }

    public static function warmUpCache(): void
    {
        // Warm up critical caches
        // This can be called during deployment or via a scheduled command

        // Example: Pre-load dashboard stats
        app(DashboardStatsOverview::class)->getCachedData();

        // Example: Pre-load navigation
        // Navigation will be cached on first load
    }
}

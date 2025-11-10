<?php

namespace App\Filament\Resources\ActivityLog;

use App\Filament\Resources\ActivityLog\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLog\Tables\ActivityLogTable;
use App\Enums\UserRole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationGroup(): ?string
    {
        return __('resources.system_management');
    }

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        // Only admins can see activity logs
        $user = auth()->user();

        return $user && $user->role === UserRole::ADMIN;
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.activity_logs');
    }

    public static function getModelLabel(): string
    {
        return __('resources.activity_log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.activity_logs');
    }

    public static function table(Table $table): Table
    {
        return ActivityLogTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }


    public static function getNavigationBadge(): ?string
    {
        $todayCount = static::getModel()::whereDate('created_at', today())->count();

        return $todayCount > 0 ? (string) $todayCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $todayCount = static::getModel()::whereDate('created_at', today())->count();

        if ($todayCount > 100) {
            return 'danger';
        }
        if ($todayCount > 50) {
            return 'warning';
        }
        if ($todayCount > 10) {
            return 'info';
        }

        return 'primary';
    }

    public static function canCreate(): bool
    {
        return false; // Activity logs are created by the system, not manually
    }

    public static function canEdit($record): bool
    {
        return false; // Activity logs should not be editable
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['description', 'log_name'];
    }

    public static function getGlobalSearchResultsLimit(): int
    {
        return 5;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('resources.log_name') => $record->log_name ?: '—',
            __('resources.event') => ucfirst($record->event ?? '—'),
            __('resources.user') => $record->causer?->name ?? __('resources.system'),
            __('resources.timestamp') => $record->created_at->diffForHumans(),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['causer', 'subject']);
    }
}

<?php

namespace App\Filament\Resources\ActivityLog\Pages;

use App\Filament\Resources\ActivityLog\ActivityLogResource;
use Filament\Actions\Action;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clean_old_logs')
                ->label('Clean Old Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clean Old Activity Logs')
                ->modalDescription('This will delete activity logs older than 90 days. Are you sure?')
                ->action(function () {
                    $deleted = \Spatie\Activitylog\Models\Activity::where('created_at', '<', now()->subDays(90))->delete();

                    \Filament\Notifications\Notification::make()
                        ->title('Logs Cleaned')
                        ->body("Deleted {$deleted} old activity logs.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Activities')
                ->badge(fn () => Activity::query()->count()),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => Activity::query()->whereDate('created_at', today())->count())
                ->icon('heroicon-m-calendar-days'),

            'users' => Tab::make('User Activities')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('subject_type', User::class))
                ->badge(fn () => Activity::query()->where('subject_type', User::class)->count())
                ->icon('heroicon-m-user-group'),

            'vehicles' => Tab::make('Vehicle Activities')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('subject_type', Vehicle::class))
                ->badge(fn () => Activity::query()->where('subject_type', Vehicle::class)->count())
                ->icon('heroicon-m-truck'),

            'bookings' => Tab::make('Booking Activities')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('subject_type', Booking::class))
                ->badge(fn () => Activity::query()->where('subject_type', Booking::class)->count())
                ->icon('heroicon-m-calendar'),

            'auth' => Tab::make('Authentication')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('description', ['User logged in', 'User logged out', 'Login attempt']))
                ->badge(fn () => Activity::query()->whereIn('description', ['User logged in', 'User logged out', 'Login attempt'])->count())
                ->icon('heroicon-m-key'),

            'errors' => Tab::make('Errors & Issues')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('log_name', 'error')
                    ->orWhere('description', 'like', '%error%')
                    ->orWhere('description', 'like', '%failed%'))
                ->badge(fn () => Activity::query()->where('log_name', 'error')
                    ->orWhere('description', 'like', '%error%')
                    ->orWhere('description', 'like', '%failed%')
                    ->count())
                ->icon('heroicon-m-exclamation-triangle'),

            'recent' => Tab::make('Recent (24h)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subHours(24)))
                ->badge(fn () => Activity::query()->where('created_at', '>=', now()->subHours(24))->count())
                ->icon('heroicon-m-clock'),
        ];
    }
}

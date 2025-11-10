<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        $user = auth()->user();

        return [
            'all' => Tab::make(__('resources.all_bookings'))
                ->badge(fn (): string => (string) Booking::count()),

            'active' => Tab::make(__('resources.active_bookings'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->active())
                ->badge(fn (): string => (string) Booking::query()->active()->count())
                ->badgeColor('success'),

            'upcoming' => Tab::make(__('resources.upcoming_bookings'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->upcoming(7)) // next 7 days
                ->badge(fn (): string => (string) Booking::query()->upcoming(7)->count())
                ->badgeColor('info'),

            'overdue' => Tab::make(__('resources.overdue_bookings'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->overdue())
                ->badge(fn (): string => (string) Booking::query()->overdue()->count())
                ->badgeColor('danger'),

            'attention' => Tab::make(__('resources.needs_attention'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->requiringAttention())
                ->badge(fn (): string => (string) Booking::query()->requiringAttention()->count())
                ->badgeColor('warning'),

            'revenue_this_month' => Tab::make(__('resources.revenue_this_month'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->revenueInPeriod(
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                    $user->role === UserRole::OWNER ? $user->id : null
                )
                )
                ->badge(fn (): string => (string) Booking::query()->revenueInPeriod(
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                    $user->role === UserRole::OWNER ? $user->id : null
                )->count())
                ->badgeColor('primary'),
        ];
    }
}

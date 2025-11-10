<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\VehicleResource\Schemas\VehicleInfolist;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularVehiclesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->withCount('bookings')
                    ->withAvg('reviews', 'rating')
                    ->where('status', 'published')
                    ->orderBy('bookings_count', 'desc')
                    ->limit(8)
            )
            ->columns([

                Tables\Columns\TextColumn::make('make')
                    ->label(__('widgets.vehicle'))
                    ->formatStateUsing(fn ($record): string => "{$record->make} {$record->model} ({$record->year})")
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\BadgeColumn::make('category')
                    ->label(__('widgets.category'))
                    ->colors([
                        'success' => __('enums.vehicle_category.economy'),
                        'info' => __('enums.vehicle_category.compact'),
                        'warning' => __('enums.vehicle_category.midsize'),
                        'primary' => __('enums.vehicle_category.fullsize'),
                        'danger' => __('enums.vehicle_category.luxury'),
                        'gray' => __('enums.vehicle_category.suv'),
                    ]),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->label(__('widgets.bookings'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviews_avg_rating')
                    ->label(__('widgets.rating'))
                    ->formatStateUsing(fn ($state): string|array|null => $state ? number_format($state, 1).'/5 ⭐' : __('widgets.no_ratings'))
                    ->color('warning'),

                Tables\Columns\TextColumn::make('daily_rate')
                    ->label(__('widgets.daily_rate'))
                    ->money(config('app.currency', 'MYR'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label(__('widgets.revenue'))
                    ->state(fn ($record) => $record->bookings()
                        ->where('car_rental_bookings.status', 'completed')
                        ->sum('total_amount'))
                    ->money(config('app.currency', 'MYR'))
                    ->color('success'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('widgets.status'))
                    ->colors([
                        'success' => 'published',
                        'warning' => 'draft',
                        'danger' => 'maintenance',
                        'gray' => 'archived',
                    ]),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('widgets.view'))
                    ->icon('heroicon-m-eye')
                    ->modalHeading(fn (Vehicle $vehicle): string => __('resources.vehicle').': '.$vehicle->make.' '.$vehicle->model)
                    ->infolist(fn (): array => VehicleInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),

                Action::make('book')
                    ->label(__('widgets.book'))
//                    ->icon('heroicon-m-calendar-plus')
                    ->color('success')
                    ->url(fn (Vehicle $vehicle): string => route('filament.admin.resources.bookings.create', ['vehicle_id' => $vehicle->id])
                    ),
            ])
            ->heading(__('widgets.popular_vehicles'))
            ->description(__('widgets.top_performing_vehicles'));
    }
}

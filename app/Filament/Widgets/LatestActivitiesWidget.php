<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActivityLogResource\Schemas\ActivityLogInfolist;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Activitylog\Models\Activity;

class LatestActivitiesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label(__('resources.activity'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $textColumn): ?string {
                        $state = $textColumn->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\BadgeColumn::make('event')
                    ->label(__('widgets.event'))
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                        'info' => 'viewed',
                        'primary' => fn ($state): bool => in_array($state, ['logged_in', 'logged_out']),
                        'gray' => 'default',
                    ]),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('widgets.subject'))
                    ->formatStateUsing(fn ($state): string => $state ? class_basename($state) : 'System')
                    ->badge()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('resources.user'))
                    ->searchable()
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('causer.role')
                    ->label(__('resources.role'))
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'owner',
                        'success' => 'renter',
                    ])
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('widgets.when'))
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('Y-m-d H:i:s')),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('resources.view'))
                    ->icon('heroicon-m-eye')
                    ->modalHeading(fn (Activity $activity): string => __('resources.activity_log').' #'.$activity->id)
                    ->infolist(fn (): array => ActivityLogInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),
            ])
            ->poll('30s')
            ->heading(__('widgets.latest_activities'))
            ->description(__('widgets.recent_system_activities_desc'));
    }
}

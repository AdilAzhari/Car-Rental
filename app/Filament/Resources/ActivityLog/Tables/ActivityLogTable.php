<?php

namespace App\Filament\Resources\ActivityLog\Tables;

use App\Filament\Resources\ActivityLog\Schemas\ActivityLogInfolist;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogTable
{
    public static function configure(Table $table): Table
    {
        return $table
           ->columns([
                TextColumn::make('id')
                    ->label(__('resources.id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('log_name')
                    ->label(__('resources.log_name'))
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('description')
                    ->label(__('resources.activity'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $textColumn): ?string {
                        $state = $textColumn->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                BadgeColumn::make('event')
                    ->label(__('resources.event'))
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                        'info' => 'viewed',
                        'primary' => fn ($state): bool => in_array($state, ['logged_in', 'logged_out']),
                        'gray' => 'default',
                    ]),

                TextColumn::make('subject_type')
                    ->label(__('resources.subject'))
                    ->formatStateUsing(fn ($state): string|array|null => $state ? class_basename($state) : __('resources.system'))
                    ->badge()
                    ->color('secondary')
                    ->sortable(),

                TextColumn::make('subject_id')
                    ->label(__('resources.subject_id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('causer.name')
                    ->label(__('resources.user'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('resources.system')),

                TextColumn::make('causer.role')
                    ->label(__('resources.role'))
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'owner',
                        'success' => 'renter',
                    ])
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('resources.timestamp'))
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('Y-m-d H:i:s')),

                TextColumn::make('properties')
                    ->label(__('resources.properties'))
                    ->formatStateUsing(function ($record) {
                        if (! $record->properties || $record->properties->isEmpty()) {
                            return __('resources.none');
                        }

                        $count = $record->properties->count();

                        return $count.' '.__('resources.items');
                    })
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Log Type')
                    ->options([
                        'vehicle' => 'Vehicle',
                        'booking' => 'Booking',
                        'payment' => 'Payment',
                        'user' => 'User',
                        'review' => 'Review',
                    ])
                    ->multiple(),

                SelectFilter::make('event')
                    ->label('Event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                    ])
                    ->multiple(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                 ViewAction::make()
                    ->modalHeading(fn ($record): string => __('resources.activity_log').' #'.$record->id)
                    ->infolist(fn (): array => ActivityLogInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('resources.archive_selected'))
                        ->modalHeading(__('resources.archive_activity_logs'))
                        ->modalDescription(__('resources.archive_activity_logs_confirmation')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
}

<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\VehicleStatus;
use App\Filament\Resources\Vehicles\Schemas\VehicleForm;
use App\Filament\Resources\Vehicles\Schemas\VehicleInfolist;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';

    protected static ?string $recordTitleAttribute = 'make';

    public function form(Schema $schema): Schema
    {
        return VehicleForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('make')
                    ->label(__('resources.make'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model')
                    ->label(__('resources.model'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('year')
                    ->label(__('resources.year'))
                    ->sortable(),

                TextColumn::make('license_plate')
                    ->label(__('resources.license_plate'))
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label(__('resources.status'))
                    ->getStateUsing(fn ($record): string => $record->status instanceof VehicleStatus ? $record->status->label() : (string) $record->status)
                    ->colors([
                        'success' => 'available',
                        'warning' => 'maintenance',
                        'danger' => 'unavailable',
                        'info' => 'rented',
                    ]),

                TextColumn::make('daily_rate')
                    ->label(__('resources.daily_rate'))
                    ->money(config('app.currency', 'MYR'))
                    ->sortable(),

                TextColumn::make('bookings_count')
                    ->label(__('resources.total_bookings'))
                    ->counts('bookings')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(__('resources.add_vehicle'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['owner_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn ($record): string => __('resources.vehicle').': '.$record->make.' '.$record->model)
                    ->infolist(fn (): array => VehicleInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),
                EditAction::make()
                    ->modalHeading(fn ($record): string => __('resources.edit_vehicle').': '.$record->make.' '.$record->model),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]))
            ->defaultSort('created_at', 'desc');
    }
}

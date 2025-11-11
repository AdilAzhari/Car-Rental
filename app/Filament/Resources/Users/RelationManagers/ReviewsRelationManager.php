<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Resources\Reviews\Schemas\ReviewInfolist;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('resources.review_id'))
                    ->sortable(),

                TextColumn::make('vehicle.make')
                    ->label(__('resources.vehicle'))
                    ->formatStateUsing(fn ($record): string => "{$record->vehicle->make} {$record->vehicle->model}")
                    ->searchable(['make', 'model']),

                TextColumn::make('booking.id')
                    ->label(__('resources.booking_id'))
                    ->formatStateUsing(fn ($state): string => 'BK-'.$state)
                    ->url(fn ($record): string => route('filament.admin.resources.bookings.edit', ['record' => $record->booking_id]))
                    ->color('info'),

                TextColumn::make('rating')
                    ->label(__('resources.rating'))
                    ->formatStateUsing(fn ($state): string => str_repeat('⭐', (int) $state).' ('.$state.'/5)')
                    ->sortable(),

                TextColumn::make('comment')
                    ->label(__('resources.comment'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('created_at')
                    ->label(__('resources.reviewed_on'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(__('resources.create_review'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['renter_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn ($record): string => __('resources.review').' #'.$record->id)
                    ->infolist(fn (): array => ReviewInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),
                EditAction::make()
                    ->modalHeading(fn ($record): string => __('resources.edit_review').' #'.$record->id),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

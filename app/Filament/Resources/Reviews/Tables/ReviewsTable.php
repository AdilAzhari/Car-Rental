<?php

namespace App\Filament\Resources\Reviews\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\ReviewResource\Schemas\ReviewInfolist;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
           ->columns([
                TextColumn::make('id')
                    ->label(__('resources.review_id'))
                    ->sortable()
                    ->searchable()
                    ->prefix('RV-'),

                TextColumn::make('renter.name')
                    ->label(__('resources.reviewer'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('vehicle.make')
                    ->label(__('resources.vehicle'))
                    ->formatStateUsing(fn ($record): string|array|null => $record->vehicle ? "{$record->vehicle->make} {$record->vehicle->model}" : __('resources.na'))
                    ->searchable(['car_rental_vehicles.make', 'car_rental_vehicles.model']),

                TextColumn::make('rating')
                    ->label(__('resources.rating'))
                    ->formatStateUsing(fn ($state): string => str_repeat('⭐', (int) $state).' ('.$state.'/5)')
                    ->sortable()
                    ->summarize([
                        Average::make()->label('Avg Rating'),
                        Count::make()->label('Total Reviews'),
                    ]),

                TextColumn::make('is_visible')
                    ->label(__('resources.visibility'))
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state): string|array|null => $state ? __('resources.visible') : __('resources.hidden')),

                TextColumn::make('comment')
                    ->label(__('resources.comment'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $textColumn): ?string {
                        $state = $textColumn->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('created_at')
                    ->label(__('resources.submitted'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                  SelectFilter::make('rating')
                    ->label(__('resources.rating'))
                    ->options([
                        '5' => __('resources.5_stars'),
                        '4' => __('resources.4_stars'),
                        '3' => __('resources.3_stars'),
                        '2' => __('resources.2_stars'),
                        '1' => __('resources.1_star'),
                    ]),

                SelectFilter::make('is_visible')
                    ->label(__('resources.visibility'))
                    ->options([
                        1 => __('resources.visible'),
                        0 => __('resources.hidden'),
                    ]),

                Filter::make('high_rating')
                    ->label(__('resources.high_rating'))
                    ->query(fn (Builder $query): Builder => $query->where('rating', '>=', 4)),

                Filter::make('low_rating')
                    ->label(__('resources.low_rating'))
                    ->query(fn (Builder $query) => $query->where('rating', '<=', 2)),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label(__('widgets.export'))
                    ->color('success')
                    ->icon('heroicon-m-arrow-down-tray'),
            ])
            ->recordActions([
                  ViewAction::make()
                    ->modalHeading(fn ($record): string => __('resources.review').' #RV-'.$record->id)
                    ->infolist(fn (): array => ReviewInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),
                Action::make('toggle_visibility')
                    ->label(fn (Review $review): string => $review->is_visible ? __('resources.hide') : __('resources.show'))
                    ->icon(fn (Review $review): string => $review->is_visible ? 'heroicon-m-eye-slash' : 'heroicon-m-eye')
                    ->color(fn (Review $review): string => $review->is_visible ? 'warning' : 'success')
                    ->action(function (Review $review): void {
                        $review->update(['is_visible' => ! $review->is_visible]);
                    })
                    ->requiresConfirmation()
                    ->modalDescription(fn (Review $review): string => $review->is_visible
                            ? __('resources.hide_review_confirmation')
                            : __('resources.show_review_confirmation')
                    ),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('hide_selected')
                        ->label(__('resources.hide_selected'))
                        ->icon('heroicon-m-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(fn (Review $review) => $review->update(['is_visible' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalDescription(__('resources.bulk_hide_confirmation'))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('show_selected')
                        ->label(__('resources.show_selected'))
                        ->icon('heroicon-m-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(fn (Review $review) => $review->update(['is_visible' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalDescription(__('resources.bulk_show_confirmation'))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                    FilamentExportBulkAction::make('bulk_export')
                        ->label(__('widgets.export'))
                        ->icon('heroicon-m-arrow-down-tray'),
                ]),
            ]);
    }
}

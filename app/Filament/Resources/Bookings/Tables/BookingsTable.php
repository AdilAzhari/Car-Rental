<?php

namespace App\Filament\Resources\Bookings\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Bookings\Schemas\BookingInfolist;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->columns([
                TextColumn::make('renter.name')
                    ->label(__('resources.renter'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicle.model')
                    ->label(__('resources.vehicle'))
                    ->formatStateUsing(fn ($record): string => $record->vehicle ? ($record->vehicle->make.' '.$record->vehicle->model) : 'N/A')
                    ->searchable(['make', 'model'])
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label(__('resources.start_date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(__('resources.end_date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('days')
                    ->label(__('resources.days'))
                    ->numeric()
                    ->sortable(false)
                    ->summarize([
                        Sum::make()->label('Total Days Booked'),
                        Average::make()->label('Avg Booking Duration'),
                    ]),

                TextColumn::make('total_amount')
                    ->label(__('resources.total_amount'))
                    ->money(config('app.currency', 'MYR'))
                    ->sortable()
                    ->summarize([
                        Sum::make()->money(config('app.currency', 'MYR'))->label('Total Revenue'),
                        Average::make()->money(config('app.currency', 'MYR'))->label('Average'),
                        Count::make()->label('Total Bookings'),
                    ]),

                BadgeColumn::make('status')
                    ->label(__('resources.booking_status'))
                    ->formatStateUsing(fn ($state): string => $state instanceof BookingStatus ? $state->label() : (string) $state)
                    ->getStateUsing(fn ($record): string => $record->status instanceof BookingStatus ? $record->status->label() : (string) $record->status)
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'ongoing',
                        'primary' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                BadgeColumn::make('payment_status')
                    ->label(__('resources.payment_status'))
                    ->formatStateUsing(fn ($state): string => $state instanceof PaymentStatus ? $state->label() : (string) $state)
                    ->getStateUsing(fn ($record): string => $record->payment_status instanceof PaymentStatus ? $record->payment_status->label() : (string) $record->payment_status)
                    ->colors([
                        'warning' => 'unpaid',
                        'success' => 'paid',
                        'secondary' => 'refunded',
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('resources.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label(__('resources.booking_status'))
                    ->options(BookingStatus::class)
                    ->multiple(),

            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label(__('widgets.export'))
                    ->color('success')
                    ->icon('heroicon-m-arrow-down-tray'),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make()
                    ->modalHeading(fn ($record): string => __('resources.booking').' #'.$record->id)
                    ->infolist(fn (): array => BookingInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),

                Action::make('confirm_booking')
                    ->label('Confirm Booking')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    // ->visible(fn ($record)  instanceof : bool => $record->status === BookingStatus::PENDING)
                    ->visible(fn ($record): bool =>
                        ($record->status instanceof BookingStatus
                                    ? $record->status
                                    : BookingStatus::tryFrom($record->status)
                                ) === BookingStatus::PENDING
                            )
                    // ->requiresConfirmation()
                    ->modalHeading('Confirm Booking')
                    ->modalDescription('Are you sure you want to confirm this booking? This will change the status to confirmed.')
                    ->modalSubmitActionLabel('Confirm Booking')
                    ->form([
                        Textarea::make('notes')
                            ->label('Confirmation Notes')
                            ->placeholder('Add any notes about the booking confirmation...')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => BookingStatus::CONFIRMED,
                        ]);

                        // Add a note to special_requests if provided
                        if (! empty($data['notes'])) {
                            $existingNotes = $record->special_requests;
                            $record->update([
                                'special_requests' => $existingNotes ? $existingNotes."\n\nAdmin Notes: ".$data['notes'] : 'Admin Notes: '.$data['notes'],
                            ]);
                        }
                    }),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Vehicles\RelationManagers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Bookings\Schemas\BookingInfolist;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('resources.booking_id'))
                    ->formatStateUsing(fn ($state): string => 'BK-'.$state)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('renter.name')
                    ->label(__('resources.renter'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): string => route('filament.admin.resources.users.edit', ['record' => $record->renter_id]))
                    ->color('primary'),

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
                    ->sortable(false),

                BadgeColumn::make('status')
                    ->label(__('resources.booking_status'))
                    ->getStateUsing(fn ($record): string => $record->status instanceof BookingStatus ? $record->status->label() : (string) $record->status)
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'ongoing',
                        'primary' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                BadgeColumn::make('payment_status')
                    ->label(__('resources.payment_status'))
                    ->getStateUsing(fn ($record): string => $record->payment_status instanceof PaymentStatus ? $record->payment_status->label() : (string) $record->payment_status)
                    ->colors([
                        'warning' => 'unpaid',
                        'success' => 'paid',
                        'secondary' => 'refunded',
                    ]),

                TextColumn::make('total_amount')
                    ->label(__('resources.total_amount'))
                    ->money(config('app.currency', 'MYR'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('resources.booked_on'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('resources.booking_status'))
                    ->options(BookingStatus::class),

                SelectFilter::make('payment_status')
                    ->label(__('resources.payment_status'))
                    ->options(PaymentStatus::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(__('resources.create_booking'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['vehicle_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn ($record): string => __('resources.booking').' #BK-'.$record->id)
                    ->infolist(fn (): array => BookingInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),
                EditAction::make()
                    ->modalHeading(fn ($record): string => __('resources.edit_booking').' #BK-'.$record->id),
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

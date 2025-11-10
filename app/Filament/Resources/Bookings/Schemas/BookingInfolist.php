<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Exception;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class BookingInfolist
{
    /**
     * @throws Exception
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Booking Overview Section
                Section::make('Booking Overview')
                    ->description('Complete information about this booking')
                    ->icon('heroicon-m-calendar')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])
                            ->schema([
                                TextEntry::make('id')
                                    ->label(__('resources.booking_id'))
                                    ->icon('heroicon-m-hashtag')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg')
                                    ->color('primary'),

                                TextEntry::make('status')
                                    ->label(__('resources.status'))
                                    ->icon('heroicon-m-flag')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => match (true) {
                                        $state instanceof BookingStatus => $state->label(),
                                        is_string($state) => BookingStatus::from($state)->label(),
                                        default => 'Unknown'
                                    })
                                    ->color(fn ($state): string => match ($state instanceof BookingStatus ? $state : BookingStatus::from($state)) {
                                        BookingStatus::PENDING => 'warning',
                                        BookingStatus::CONFIRMED => 'info',
                                        BookingStatus::ONGOING => 'primary',
                                        BookingStatus::COMPLETED => 'success',
                                        BookingStatus::CANCELLED => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('payment_status')
                                    ->label(__('resources.payment_status'))
                                    ->icon('heroicon-m-currency-dollar')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => match (true) {
                                        $state instanceof PaymentStatus => $state->label(),
                                        is_string($state) => PaymentStatus::from($state)->label(),
                                        default => 'Unknown'
                                    })
                                    ->color(fn ($state): string => match ($state instanceof PaymentStatus ? $state : PaymentStatus::from($state)) {
                                        PaymentStatus::PENDING => 'warning',
                                        PaymentStatus::CONFIRMED => 'success',
                                        PaymentStatus::FAILED, PaymentStatus::UNPAID => 'danger',
                                        PaymentStatus::REFUNDED => 'info',
                                        PaymentStatus::CANCELLED => 'secondary',
                                        PaymentStatus::PROCESSING => 'primary',
                                        default => 'gray',
                                    }),

                                TextEntry::make('total_amount')
                                    ->label(__('resources.total_amount'))
                                    ->icon('heroicon-m-currency-dollar')
                                    ->money(config('app.currency', 'MYR'))
                                    ->size('lg')
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                            ]),
                    ]),

                // Customer & Vehicle Section
                Section::make('Customer & Vehicle Details')
                    ->description('Information about the renter and vehicle')
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextEntry::make('renter.name')
                                    ->label(__('resources.renter'))
                                    ->icon('heroicon-m-user')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),

                                TextEntry::make('renter.email')
                                    ->label(__('resources.renter_email'))
                                    ->icon('heroicon-m-envelope')
                                    ->copyable()
                                    ->copyMessage('Email copied!'),
                            ]),

                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextEntry::make('vehicle.make')
                                    ->label(__('resources.vehicle'))
                                    ->formatStateUsing(fn ($record): string => $record->vehicle ? $record->vehicle->make.' '.$record->vehicle->model.' ('.$record->vehicle->year.')' : 'N/A')
                                    ->icon('heroicon-m-truck')
                                    ->weight(FontWeight::Bold)
                                    ->color('info'),

                                TextEntry::make('vehicle.plate_number')
                                    ->label(__('resources.license_plate'))
                                    ->icon('heroicon-m-rectangle-group')
                                    ->copyable()
                                    ->copyMessage('Plate number copied!')
                                    ->weight(FontWeight::Bold),
                            ]),
                    ]),

                // Booking Timeline Section
                Section::make('Booking Timeline')
                    ->description('Important dates and duration')
                    ->icon('heroicon-m-clock')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label('Start Date & Time')
                                    ->icon('heroicon-m-play')
                                    ->dateTime()
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),

                                TextEntry::make('end_date')
                                    ->label('End Date & Time')
                                    ->icon('heroicon-m-stop')
                                    ->dateTime()
                                    ->weight(FontWeight::Bold)
                                    ->color('danger'),
                            ]),

                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('resources.booking_created'))
                                    ->icon('heroicon-m-plus-circle')
                                    ->dateTime()
                                    ->since()
                                    ->dateTimeTooltip(),

                                TextEntry::make('updated_at')
                                    ->label(__('resources.last_updated'))
                                    ->icon('heroicon-m-pencil')
                                    ->dateTime()
                                    ->since()
                                    ->dateTimeTooltip(),
                            ]),
                    ]),

                // Financial Details Section
                Section::make('Financial Details')
                    ->description('Payment and financial information')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                TextEntry::make('total_amount')
                                    ->label(__('resources.total_amount'))
                                    ->icon('heroicon-m-currency-dollar')
                                    ->money(config('app.currency', 'MYR'))
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),

                                TextEntry::make('deposit_amount')
                                    ->label(__('resources.deposit'))
                                    ->icon('heroicon-m-shield-check')
                                    ->money(config('app.currency', 'MYR'))
                                    ->placeholder('No deposit required'),

                                TextEntry::make('commission_amount')
                                    ->label(__('resources.commission'))
                                    ->icon('heroicon-m-percent-badge')
                                    ->money(config('app.currency', 'MYR'))
                                    ->placeholder('No commission'),
                            ]),

                        //                        TextEntry::make('payment_method')
                        //                            ->label(__('resources.payment_method'))
                        //                            ->icon('heroicon-m-credit-card')
                        //                            ->placeholder('Not specified')
                        //                            ->badge()
                        //                            ->color('info'),
                    ]),

                // Location & Special Requests Section
                Section::make('Additional Information')
                    ->description('Pickup, drop off, and special requests')
                    ->icon('heroicon-m-map-pin')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextEntry::make('pickup_location')
                                    ->label(__('resources.pickup_location'))
                                    ->icon('heroicon-m-map-pin')
                                    ->placeholder('Not specified'),

                                TextEntry::make('dropoff_location')
                                    ->label(__('resources.drop_off_location'))
                                    ->icon('heroicon-m-flag')
                                    ->placeholder('Not specified'),
                            ]),

                        TextEntry::make('special_requests')
                            ->label(__('resources.special_requests'))
                            ->icon('heroicon-m-chat-bubble-left-ellipsis')
                            ->placeholder('No special requests')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                // Quick Actions Section
                Section::make('Quick Actions')
                    ->schema([
                        Actions::make([
                            Action::make('edit')
                                ->label(__('resources.edit_booking'))
                                ->icon('heroicon-m-pencil-square')
                                ->url(fn ($record): string => route('filament.admin.resources.bookings.edit', $record))
                                ->color('primary')
                                ->button(),

                            Action::make('view_payment')
                                ->label(__('resources.view_payment'))
                                ->icon('heroicon-m-currency-dollar')
                                ->color('success')
                                ->button()
                                ->visible(fn ($record) => $record->payment()->exists())
                                ->url(fn ($record): string => route('filament.admin.resources.payments.edit', $record->payment)),
                        ]),
                    ])
                    ->compact(),
            ]);
    }
}

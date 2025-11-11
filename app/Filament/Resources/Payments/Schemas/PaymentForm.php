<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Section::make('Payment Information')
                    ->schema([
                        Select::make('booking_id')
                            ->label('Booking')
                            ->relationship(
                                'booking',
                                'id',
                                modifyQueryUsing: fn ($query) => $query->with(['renter', 'vehicle'])
                            )
                            ->getOptionLabelFromRecordUsing(function ($record): string {
                                if (! $record) {
                                    return 'Unknown Booking';
                                }

                                $renterName = 'Unknown Renter';
                                $vehicleName = 'Unknown Vehicle';

                                try {
                                    if ($record->renter && ! empty($record->renter->name)) {
                                        $renterName = $record->renter->name;
                                    }

                                    if ($record->vehicle && ! empty($record->vehicle->make) && ! empty($record->vehicle->model)) {
                                        $vehicleName = "{$record->vehicle->make} {$record->vehicle->model}";
                                    }
                                } catch (\Exception) {
                                    // Fallback in case of any relationship errors
                                }

                                return "Booking #{$record->id} - {$renterName} ({$vehicleName})";
                            })
                            ->getOptionLabelUsing(function ($value): string {
                                if (! $value) {
                                    return 'Unknown Booking';
                                }

                                try {
                                    $booking = \App\Models\Booking::with(['renter', 'vehicle'])->find($value);

                                    if (! $booking) {
                                        return "Booking #{$value} (Not Found)";
                                    }

                                    $renterName = $booking->renter?->name ?? 'Unknown Renter';
                                    $vehicleName = $booking->vehicle
                                        ? "{$booking->vehicle->make} {$booking->vehicle->model}"
                                        : 'Unknown Vehicle';

                                    return "Booking #{$booking->id} - {$renterName} ({$vehicleName})";
                                } catch (\Exception) {
                                    return "Booking #{$value} (Error Loading)";
                                }
                            })
                            ->searchable()
                            ->required(),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('RM')
                            ->step('0.01')
                            ->required(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(
                                collect(PaymentMethod::cases())
                                    ->mapWithKeys(function ($case): array {
                                        $label = $case->label();

                                        return [$case->value => $label ?: $case->value];
                                    })
                                    ->filter()
                                    ->toArray()
                            )
                            ->required(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options(
                                collect(PaymentStatus::cases())
                                    ->mapWithKeys(function ($case): array {
                                        $label = $case->label();

                                        return [$case->value => $label ?: $case->value];
                                    })
                                    ->filter()
                                    ->toArray()
                            )
                            ->default(PaymentStatus::UNPAID->value)
                            ->required(),

                        TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->unique(ignoreRecord: true),

                        Textarea::make('gateway_response')
                            ->label('Gateway Response')
                            ->columnSpanFull()
                            ->rows(3),

                        DateTimePicker::make('processed_at')
                            ->label('Processed At'),

                        TextInput::make('refund_amount')
                            ->label('Refund Amount')
                            ->numeric()
                            ->prefix('RM')
                            ->step('0.01'),

                        DateTimePicker::make('refunded_at')
                            ->label('Refunded At'),
                    ])
                    ->columns(2),
            ]);
    }
}

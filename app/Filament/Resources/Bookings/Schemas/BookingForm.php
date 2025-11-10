<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.booking_information'))
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('renter_id')
                                    ->label(__('resources.renter'))
                                    ->relationship('renter', 'name')
                                    ->searchable()
                                    ->required()
                                    ->visible(fn (): bool => auth()->user()->role !== UserRole::RENTER),

                                Select::make('vehicle_id')
                                    ->label(__('resources.vehicle'))
                                    ->relationship(
                                        'vehicle',
                                        modifyQueryUsing: fn ($query) => $query
                                            ->select(['id', 'make', 'model', 'plate_number', 'daily_rate'])
                                            ->when(auth()->user()->role === UserRole::RENTER, fn ($q) => $q->where('status', 'published')->where('is_available', true))
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Vehicle $vehicle): string => "$vehicle->make $vehicle->model ($vehicle->plate_number)")
                                    ->searchable(['make', 'model', 'plate_number'])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state, $livewire): void {
                                        if ($state) {
                                            // Get vehicle from the cached relationship data instead of querying again
                                            $vehicle = Vehicle::query()->select(['id', 'daily_rate'])->find($state);
                                            if ($vehicle) {
                                                $set('daily_rate', $vehicle->daily_rate);
                                                self::calculateTotals($set, $get);
                                            }
                                        }
                                    }),
                            ]),

                        Grid::make()
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label(__('resources.start_date'))
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        self::calculateTotals($set, $get);
                                    }),

                                DatePicker::make('end_date')
                                    ->label(__('resources.end_date'))
                                    ->required()
                                    ->native(false)
                                    ->after('start_date')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        self::calculateTotals($set, $get);
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('days')
                                    ->label(__('resources.number_of_days'))
                                    ->numeric()
                                    ->readonly(),

                                TextInput::make('daily_rate')
                                    ->label(__('resources.daily_rate'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        self::calculateTotals($set, $get);
                                    })
                                    ->readonly(),

                                TextInput::make('subtotal')
                                    ->label(__('resources.subtotal'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->readonly(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('insurance_fee')
                                    ->label(__('resources.insurance_fee'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->readonly(),

                                TextInput::make('tax_amount')
                                    ->label(__('resources.tax_amount'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->readonly(),

                                TextInput::make('total_amount')
                                    ->label(__('resources.total_amount'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->readonly(),
                            ]),
                    ]),

                Section::make(__('resources.vehicle_status_location'))
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('status')
                                    ->label(__('resources.booking_status'))
                                    ->options(
                                        collect(BookingStatus::cases())
                                            ->mapWithKeys(fn (BookingStatus $bookingStatus): array => [
                                                $bookingStatus->value => $bookingStatus->label(),
                                            ])
                                    )
                                    ->required(),

                                Select::make('payment_status')
                                    ->label(__('resources.payment_status'))
                                    ->options(PaymentStatus::class)
                                    ->required(),
                            ]),

                        TextInput::make('pickup_location')
                            ->label(__('resources.pickup_location'))
                            ->maxLength(255),

                        TextInput::make('dropoff_location')
                            ->label(__('resources.dropoff_location'))
                            ->maxLength(255),

                        Textarea::make('special_requests')
                            ->label(__('resources.special_requests'))
                            ->rows(3)
                            ->maxLength(500),
                    ]),

                Section::make(__('resources.financial_management'))
                            ->schema([
                                TextInput::make('deposit_amount')
                                    ->label(__('resources.deposit_amount'))
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0.0),

                                TextInput::make('commission_amount')
                                    ->label(__('resources.commission_amount'))
                                    ->required()
                                    ->numeric()
                                    ->prefix('MYR')
                                    ->default(0.0),

                                Select::make('payment_method')
                                    ->label(__('resources.payment_method'))
                                    ->options(PaymentMethod::class)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
            ]);
    }
    private static function calculateTotals(Set $set, Get $get): void
    {
        $startDate = $get('start_date');
        $endDate = $get('end_date');
        $dailyRate = (float) $get('daily_rate');

        // Reset all fields if required data is missing
        if (! $startDate || ! $endDate || ! $dailyRate) {
            $set('days', null);
            $set('subtotal', null);
            $set('insurance_fee', null);
            $set('tax_amount', null);
            $set('total_amount', null);

            return;
        }

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            // Calculate days (inclusive of both start and end date)
            $days = $start->diffInDays($end) + 1;

            if ($days <= 0) {
                $set('days', 0);
                $set('subtotal', '0.00');
                $set('insurance_fee', '0.00');
                $set('tax_amount', '0.00');
                $set('total_amount', '0.00');

                return;
            }

            // Calculate all amounts
            $subtotal = $days * $dailyRate;
            $insuranceFee = $subtotal * 0.10; // 10% insurance fee
            $taxAmount = $subtotal * 0.15; // 15% tax
            $totalAmount = $subtotal + $insuranceFee + $taxAmount;

            // Set calculated values
            $set('days', $days);
            $set('subtotal', number_format($subtotal, 2, '.', ''));
            $set('insurance_fee', number_format($insuranceFee, 2, '.', ''));
            $set('tax_amount', number_format($taxAmount, 2, '.', ''));
            $set('total_amount', number_format($totalAmount, 2, '.', ''));
        } catch (\Exception) {
            // Handle date parsing errors - reset fields
            $set('days', null);
            $set('subtotal', null);
            $set('insurance_fee', null);
            $set('tax_amount', null);
            $set('total_amount', null);
        }
    }
}

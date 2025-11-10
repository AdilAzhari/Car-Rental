<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Enums\VehicleFuelType;
use App\Enums\VehicleTransmission;
use App\Enums\VehicleCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VehicleStatus;
use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Filament\Resources\VehicleResource\Schemas\VehicleInfolist;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\FilamentQueryOptimizationService;
use App\Services\TrafficViolationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;
use UnitEnum;


class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.vehicle_information'))
                    ->description(__('resources.basic_information'))
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('make')
                                    ->label(__('resources.make'))
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder(__('resources.make_placeholder'))
                                    ->validationMessages([
                                        'required' => __('resources.make_required'),
                                        'max' => __('resources.make_max_length'),
                                    ]),

                                TextInput::make('model')
                                    ->label(__('resources.model'))
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder(__('resources.model_placeholder'))
                                    ->validationMessages([
                                        'required' => __('resources.model_required'),
                                        'max' => __('resources.model_max_length'),
                                    ]),

                                TextInput::make('year')
                                    ->label(__('resources.year'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1990)
                                    ->maxValue(now()->year + 1)
                                    ->placeholder(__('resources.year_placeholder'))
                                    ->validationMessages([
                                        'required' => __('resources.year_required'),
                                        'numeric' => __('resources.year_numeric'),
                                        'min' => __('resources.year_min'),
                                        'max' => __('resources.year_max'),
                                    ]),
                            ]),

                        TextInput::make('plate_number')
                            ->label(__('resources.plate_number'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder(__('resources.plate_number'))
                            ->suffixIcon('heroicon-m-identification'),
                    ])
                    ->columnSpanFull(),

                Section::make(__('resources.vehicle_categories'))
                    ->description(__('resources.vehicle_categories_description'))
                    ->icon('heroicon-m-tag')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('category')
                                    ->label(__('resources.category'))
                                    ->options(VehicleCategory::class)
                                    ->required()
                                    ->native(false),

                                Select::make('transmission')
                                    ->label(__('resources.transmission'))
                                    ->options([
                                        'automatic' => __('enums.transmission.automatic'),
                                        'manual' => __('enums.transmission.manual'),
                                        'cvt' => __('enums.transmission.cvt'),
                                    ])
                                    ->required()
                                    ->native(false),

                                Select::make('fuel_type')
                                    ->label(__('resources.fuel_type'))
                                    ->options([
                                        'petrol' => __('enums.fuel_type.petrol'),
                                        'diesel' => __('enums.fuel_type.diesel'),
                                        'hybrid' => __('enums.fuel_type.hybrid'),
                                        'electric' => __('enums.fuel_type.electric'),
                                        'lpg' => __('enums.fuel_type.lpg'),
                                    ])
                                    ->required()
                                    ->native(false),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('seats')
                                    ->label(__('resources.seats'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(50)
                                    ->default(5)
                                    ->suffixIcon('heroicon-m-user-group'),

                                TextInput::make('color')
                                    ->label(__('resources.color'))
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder(__('resources.color_placeholder')),

                                TextInput::make('engine_size')
                                    ->label(__('resources.engine_size'))
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0.1)
                                    ->maxValue(99.9)
                                    ->placeholder(__('resources.engine_size_placeholder'))
                                    ->suffix('L')
                                    ->helperText(__('resources.engine_size_helper'))
                                    ->validationMessages([
                                        'max' => __('resources.engine_size_max_error'),
                                        'min' => __('resources.engine_size_min_error'),
                                        'numeric' => __('resources.engine_size_numeric_error'),
                                    ]),

                                TextInput::make('mileage')
                                    ->label(__('resources.mileage_km'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->placeholder(__('resources.mileage_placeholder')),
                            ]),
                    ]),

                Section::make(__('resources.ownership_pricing'))
                    ->description(__('resources.ownership_pricing_description'))
                    ->icon('heroicon-m-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('owner_id')
                                    ->label(__('resources.vehicle_owner'))
                                    ->relationship('owner', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('resources.name'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder(__('resources.enter_full_name'))
                                                    ->columnSpanFull(),

                                                TextInput::make('email')
                                                    ->label(__('resources.email'))
                                                    ->email()
                                                    ->required()
                                                    ->unique('car_rental_users', 'email')
                                                    ->maxLength(255)
                                                    ->placeholder(__('resources.email_placeholder')),

                                                TextInput::make('phone')
                                                    ->label(__('resources.phone'))
                                                    ->tel()
                                                    ->maxLength(20)
                                                    ->placeholder(__('resources.phone_placeholder')),

                                                TextInput::make('password')
                                                    ->label(__('resources.password'))
                                                    ->password()
                                                    ->required()
                                                    ->minLength(8)
                                                    ->maxLength(255)
                                                    ->placeholder(__('resources.password_placeholder'))
                                                    ->revealable()
                                                    ->helperText(__('resources.password_helper')),

                                                TextInput::make('password_confirmation')
                                                    ->label(__('resources.confirm_password'))
                                                    ->password()
                                                    ->required()
                                                    ->same('password')
                                                    ->maxLength(255)
                                                    ->placeholder(__('resources.confirm_password_placeholder'))
                                                    ->revealable()
                                                    ->dehydrated(false),

                                                Select::make('role')
                                                    ->label(__('resources.role'))
                                                    ->options([
                                                        UserRole::OWNER->value => __('resources.owner'),
                                                        UserRole::RENTER->value => __('resources.renter'),
                                                    ])
                                                    ->default(UserRole::OWNER->value)
                                                    ->required()
                                                    ->native(false)
                                                    ->helperText(__('resources.user_role_helper')),
                                            ]),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        $user = User::create([
                                            'name' => $data['name'],
                                            'email' => $data['email'],
                                            'phone' => $data['phone'] ?? null,
                                            'password' => $data['password'],
                                            'role' => $data['role'],
                                            'status' => UserStatus::ACTIVE,
                                            'is_verified' => true,
                                            'is_new_user' => false,
                                            'has_changed_default_password' => true,
                                            'password_changed_at' => now(),
                                        ]);

                                        return $user->id;
                                    })
                                    ->placeholder(__('resources.select_or_create_owner'))
                                    ->helperText(__('resources.owner_helper_text'))
                                    ->default(fn () => auth()->user() && auth()->user()->role === UserRole::OWNER ? auth()->id() : null)
                                    ->hidden(fn (): bool => auth()->user() && auth()->user()->role === UserRole::OWNER),

                                TextInput::make('daily_rate')
                                    ->label(__('resources.daily_rate'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(9999999.99)
                                    ->prefix('RM')
                                    ->step(0.01)
                                    ->placeholder(__('resources.daily_rate_placeholder'))
                                    ->validationMessages([
                                        'required' => __('resources.daily_rate_required'),
                                        'numeric' => __('resources.daily_rate_numeric'),
                                        'min' => __('resources.daily_rate_min'),
                                        'max' => __('resources.daily_rate_max'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('resources.vehicle_status_location'))
                    ->description(__('resources.vehicle_status_location_description'))
                    ->icon('heroicon-m-map-pin')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label(__('resources.status'))
                                    ->options(VehicleStatus::class)
                                    ->default(VehicleStatus::DRAFT->value)
                                    ->required()
                                    ->native(false),

                                Toggle::make('is_available')
                                    ->label(__('resources.available_for_rent'))
                                    ->default(true)
                                    ->helperText(__('resources.can_customers_book')),

                                Toggle::make('insurance_included')
                                    ->label(__('resources.insurance_included'))
                                    ->default(true)
                                    ->helperText(__('resources.does_rental_include_insurance')),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('location')
                                    ->label(__('resources.current_location'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('resources.location_placeholder')),

                                TextInput::make('pickup_location')
                                    ->label(__('resources.pickup_location'))
                                    ->maxLength(255)
                                    ->placeholder(__('resources.pickup_location_placeholder')),

                                DatePicker::make('insurance_expiry')
                                    ->label(__('resources.insurance_expiry'))
                                    ->required()
                                    ->minDate(now())
                                    ->placeholder(__('resources.insurance_expiry_placeholder')),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('insurance_fine_amount')
                                    ->label('Insurance Fine Amount')
                                    ->numeric()
                                    ->prefix('RM')
                                    ->step(0.01)
                                    ->default(0.00)
                                    ->helperText('Fine amount for expired insurance'),

                                Toggle::make('insurance_fine_paid')
                                    ->label('Insurance Fine Paid')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Indicates if the fine has been paid'),

                                DateTimePicker::make('insurance_fine_paid_at')
                                    ->label('Fine Paid At')
                                    ->displayFormat('d M Y H:i')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record): bool => $record && $record->insurance_fine_paid)
                                    ->helperText('When the fine was paid'),
                            ]),
                        //                            ->visible(fn ($record): bool => $record && ($record->insurance_expiry < now() || $record->insurance_fine_amount > 0)),
                    ]),

                Section::make(__('resources.images_media'))
                    ->description(__('resources.images_media_description'))
                    ->icon('heroicon-m-photo')
                    ->schema([
                        FileUpload::make('featured_image')
                            ->label(__('resources.featured_image'))
                            ->image()
                            ->directory('vehicles/featured')
                            ->maxSize(5120)
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(800)
                            ->imageResizeTargetHeight(600),

                        FileUpload::make('gallery_images')
                            ->label(__('resources.gallery_images'))
                            ->multiple()
                            ->image()
                            ->directory('vehicles/gallery')
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(800)
                            ->imageResizeTargetHeight(600)
                            ->helperText(__('resources.gallery_images_helper')),

                        FileUpload::make('documents')
                            ->label(__('resources.documents'))
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->directory('vehicles/documents')
                            ->maxFiles(5)
                            ->helperText(__('resources.documents_helper')),
                    ])
                    ->collapsible(),

                Section::make(__('resources.features_specifications'))
                    ->description(__('resources.features_specifications_description'))
                    ->icon('heroicon-m-cog-6-tooth')
                    ->schema([
                        KeyValue::make('features')
                            ->label(__('resources.vehicle_features'))
                            ->keyLabel(__('resources.feature'))
                            ->valueLabel(__('resources.details'))
                            ->default([
                                __('resources.air_conditioning') => __('resources.yes'),
                                __('resources.bluetooth') => __('resources.yes'),
                                __('resources.gps_navigation') => __('resources.yes'),
                            ]),

                        Textarea::make('description')
                            ->label(__('resources.description'))
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder(__('resources.description_placeholder'))
                            ->columnSpanFull(),

                        Textarea::make('terms_and_conditions')
                            ->label(__('resources.terms_conditions'))
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder(__('resources.terms_conditions_placeholder'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('resources.traffic_violations'))
                    ->description(__('resources.traffic_violations_description'))
                    ->icon('heroicon-m-exclamation-triangle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('has_pending_violations')
                                    ->label(__('resources.has_pending_violations'))
                                    ->helperText(__('resources.pending_violations_helper'))
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('total_violations_count')
                                    ->label(__('resources.total_violations_count'))
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-exclamation-circle'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_fines_amount')
                                    ->label(__('resources.total_fines_amount'))
                                    ->numeric()
                                    ->prefix('RM')
                                    ->step(0.01)
                                    ->default(0.00)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-currency-dollar'),

                                DateTimePicker::make('violations_last_checked')
                                    ->label(__('resources.last_checked'))
                                    ->displayFormat('d M Y H:i')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-clock'),
                            ]),

                        // Traffic Violations Details View
                        ViewField::make('traffic_violations_display')
                            ->label(__('resources.violation_details'))
                            ->view('filament.components.traffic-violations-display')
                            ->visible(fn ($record): bool => $record && $record->traffic_violations && count($record->traffic_violations) > 0)
                            ->columnSpanFull(),

                        // API Integration Actions
                        Placeholder::make('api_integration_info')
                            ->label(__('resources.api_integration'))
                            ->content(fn (): string => __('resources.api_integration_description'))
                            ->extraAttributes([
                                'class' => 'text-sm p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),

                Section::make('Parking & Municipal Violations')
                    ->description('Track parking and municipal violations from local authorities (DBKL, MPSJ, etc.)')
                    ->icon('heroicon-m-building-office-2')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('has_pending_parking_violations')
                                    ->label('Has Pending Parking Violations')
                                    ->helperText('Indicates if there are unpaid parking violations')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('total_parking_violations_count')
                                    ->label('Total Parking Violations')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-exclamation-circle'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_parking_fines_amount')
                                    ->label('Total Parking Fines')
                                    ->numeric()
                                    ->prefix('RM')
                                    ->step(0.01)
                                    ->default(0.00)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-currency-dollar'),

                                DateTimePicker::make('parking_violations_last_checked')
                                    ->label('Last Checked')
                                    ->displayFormat('d M Y H:i')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-clock'),
                            ]),

                        Placeholder::make('parking_violations_info')
                            ->label('About Parking Violations')
                            ->content('Use the "Check Parking Violations" action in the table to check for violations from municipal authorities. Payment gateway integration is in progress.')
                            ->extraAttributes([
                                'class' => 'text-sm p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),

                Section::make(__('resources.maintenance_records'))
                    ->description(__('resources.maintenance_records_description'))
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('last_oil_change'),
                                TextInput::make('oil_type'),
                        ]),
                    ]),
                ]);
    }
}

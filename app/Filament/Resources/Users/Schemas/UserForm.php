<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(__('users.sections.personal_information'))
    ->description(__('resources.user_details_description'))
    ->icon('heroicon-m-user')
    ->schema([
        // Avatar upload with better positioning
        FileUpload::make('avatar')
            ->label(__('users.fields.avatar'))
            ->directory('user-avatars')
            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->helperText(__('resources.avatar_helper'))
            ->maxSize(2048) // 2MB limit
            ->validationMessages([
                'max' => __('resources.file_size_max_error'),
                'mimes' => __('resources.file_type_error'),
            ])
            ->image()
            ->imageEditor()
            ->avatar()
            ->alignCenter()
            ->columnSpanFull(),

        Grid::make()
            ->columns(1)
            ->schema([
                // Name field with better validation
                TextInput::make('name')
                    ->label(__('users.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('resources.enter_full_name'))
                    ->autocomplete('name')
                    ->suffixIcon('heroicon-m-user'),

                // Contact information in a 2-column grid
                Grid::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label(__('users.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder(__('resources.email_placeholder'))
                            ->autocomplete('email')
                            ->suffixIcon('heroicon-m-envelope')
                            ->prefixIcon('heroicon-m-at-symbol'),

                        TextInput::make('phone')
                            ->label(__('users.fields.phone'))
                            ->tel()
                            ->maxLength(20)
                            ->placeholder(__('resources.phone_placeholder'))
                            ->autocomplete('tel')
                            ->suffixIcon('heroicon-m-phone')
                            ->prefixIcon('heroicon-m-device-phone-mobile'),
                    ]),

                // Date of birth with better validation and formatting
                DatePicker::make('date_of_birth')
                    ->label(__('users.fields.date_of_birth'))
                    ->required()
                    ->maxDate(now()->subYears(18))
                    ->minDate(now()->subYears(100)) // Reasonable minimum age
                    ->displayFormat('Y-m-d')
                    ->format('Y-m-d')
                    ->helperText(__('resources.age_requirement'))
                    ->suffixIcon('heroicon-m-calendar')
                    ->placeholder(__('resources.select_date'))
                    ->closeOnDateSelection()
                    ->columnSpanFull(),
            ]),
        ]),

                Section::make(__('resources.account_settings'))
                    ->description(__('resources.account_settings_description'))
                    ->icon('heroicon-m-cog-6-tooth')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('role')
                                    ->label(__('resources.user_role'))
                                    ->options([
                                        'admin' => __('enums.user_role.admin'),
                                        'owner' => __('enums.user_role.owner'),
                                        'renter' => __('enums.user_role.customer'),
                                    ])
                                    ->default('renter')
                                    ->required()
                                    ->native(false),

                                Select::make('status')
                                    ->label(__('resources.account_status'))
                                    ->options(UserStatus::class)
                                    ->default('active')
                                    ->required()
                                    ->native(false)
                                    ->helperText(__('resources.user_status_helper')),

                                Toggle::make('is_verified')
                                    ->label(__('resources.account_verified'))
                                    ->helperText(__('resources.verified_users_helper'))
                                    ->default(false),
                            ]),
                    ]),

                Section::make('Password Management')
                    ->description('Set or change the user password (Admin only)')
                    ->icon('heroicon-m-key')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->label('New Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrateStateUsing(fn ($state): ?string => filled($state) ? bcrypt($state) : null)
                                    ->dehydrated(fn ($state): bool => filled($state))
                                    ->nullable()
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->helperText('Leave blank to keep current password. Minimum 8 characters.')
                                    ->placeholder('Enter new password'),

                                TextInput::make('password_confirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->same('password')
                                    ->visible(fn (Get $get): bool => filled($get('password')))
                                    ->requiredWith('password')
                                    ->placeholder('Re-enter password'),
                            ]),
                    ])
                    ->visible(fn (): bool => auth()->user()?->role === UserRole::ADMIN)
                    ->collapsible(),

                Section::make(__('resources.address_information'))
                    ->description(__('resources.address_information_description'))
                    ->icon('heroicon-m-map-pin')
                    ->collapsible()
                    ->schema([
                        Textarea::make('address')
                            ->label(__('resources.address'))
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder(__('resources.enter_full_address')),
                    ]),

                Section::make(__('resources.additional_information'))
                    ->description(__('resources.license_preferences_description'))
                    ->icon('heroicon-m-document-text')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('license_number')
                                    ->label(__('resources.license_number'))
                                    ->maxLength(50)
                                    ->helperText(__('resources.license_required_helper')),

                                DatePicker::make('license_expiry_date')
                                    ->label(__('resources.license_expiry_date'))
                                    ->minDate(now())
                                    ->displayFormat('Y-m-d'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                FileUpload::make('id_document_path')
                                    ->label(__('resources.id_document'))
                                    ->directory('user-documents/id')
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf'])
                                    ->maxSize(10240)
                                    ->downloadable()
                                    ->openable()
                                    ->previewable(true)
                                    ->helperText(__('resources.id_document_helper'))
                                    ->validationMessages([
                                        'max' => __('resources.file_size_max_error'),
                                        'mimes' => __('resources.file_type_error'),
                                    ]),

                                FileUpload::make('license_document_path')
                                    ->label(__('resources.license_document'))
                                    ->directory('user-documents/license')
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf'])
                                    ->maxSize(10240)
                                    ->downloadable()
                                    ->openable()
                                    ->previewable(true)
                                    ->helperText(__('resources.license_document_helper'))
                                    ->validationMessages([
                                        'max' => __('resources.file_size_max_error'),
                                        'mimes' => __('resources.file_type_error'),
                                    ]),
                            ]),

                        Textarea::make('notes')
                            ->label(__('resources.admin_notes'))
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->placeholder(__('resources.admin_notes_placeholder')),
                    ]),
                    
            Section::make('Account Status & Timestamps')
                ->schema([
                    // Single column for the important status toggle
                    Toggle::make('has_changed_default_password')
                        ->label('Changed Default Password')
                        ->disabled()
                        ->required(),

                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('last_login_at')
                                ->label('Last Login At')
                                ->disabled(),
                            DateTimePicker::make('password_changed_at')
                                ->label('Password Changed At')
                                ->disabled(),
                        ]),
                ]),
            ]);
    }
}

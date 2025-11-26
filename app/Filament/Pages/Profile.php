<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Override;

class Profile extends Page implements HasForms
{
    use InteractsWithForms, WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    #[Override]
    public function getView(): string
    {
        return 'filament.pages.profile';
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('resources.my_profile');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('resources.account_settings');
    }

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
            'address' => $user->address,
            'license_number' => $user->license_number,
            'avatar' => $user->avatar ? [$user->avatar] : [],
            'id_document_path' => $user->id_document_path ? [$user->id_document_path] : [],
            'license_document_path' => $user->license_document_path ? [$user->license_document_path] : [],
            'role' => $user->role?->value ?? 'renter',
            'status' => $user->status?->value ?? 'active',
            'is_verified' => $user->is_verified ?? false,
            'preferred_language' => app()->getLocale(),
            'account_created' => $user->created_at->format('d M Y'),
            'last_login_at' => $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : __('resources.never'),
            'password_changed_display' => $user->password_changed_at ? $user->password_changed_at->format('d M Y') : __('resources.never'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                // Profile Picture Section
                Section::make(__('resources.profile_picture'))
                    ->description(__('resources.profile_picture_description'))
                    ->icon('heroicon-m-camera')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label(__('resources.profile_picture'))
                            ->avatar()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('400')
                            ->directory('avatars')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText(__('resources.avatar_helper'))
                            ->columnSpanFull(),
                    ]),

                // Personal Information Section
                Section::make(__('resources.personal_information'))
                    ->description(__('resources.personal_information_description'))
                    ->icon('heroicon-m-user')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('resources.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('resources.enter_full_name'))
                                    ->helperText(__('resources.name_helper'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set): void {
                                        if (filled($state)) {
                                            $set('name', trim($state));
                                        }
                                    }),

                                TextInput::make('email')
                                    ->label(__('resources.email'))
                                    ->email()
                                    ->required()
                                    ->unique(table: 'car_rental_users', ignorable: auth()->user())
                                    ->maxLength(255)
                                    ->placeholder(__('resources.email_placeholder'))
                                    ->suffixIcon('heroicon-m-envelope'),

                                TextInput::make('phone')
                                    ->label(__('resources.phone'))
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder(__('resources.phone_placeholder'))
                                    ->suffixIcon('heroicon-m-phone')
                                    ->helperText(__('resources.phone_helper')),

                                DatePicker::make('date_of_birth')
                                    ->label(__('resources.date_of_birth'))
                                    ->maxDate(now()->subYears(18))
                                    ->displayFormat('d/m/Y')
                                    ->placeholder(__('resources.date_of_birth_placeholder'))
                                    ->helperText(__('resources.age_requirement'))
                                    ->suffixIcon('heroicon-m-calendar'),

                                Select::make('preferred_language')
                                    ->label(__('resources.preferred_language'))
                                    ->options([
                                        'en' => __('resources.english'),
                                        'ar' => __('resources.arabic'),
                                    ])
                                    ->default('en')
                                    ->native(false)
                                    ->suffixIcon('heroicon-m-language'),

                                Select::make('role')
                                    ->label(__('resources.account_type'))
                                    ->options([
                                        'renter' => __('enums.user_role.renter'),
                                        'owner' => __('enums.user_role.owner'),
                                    ])
                                    ->default('owner')
                                    ->native(false)
                                    ->disabled()
                                    ->hidden()
                                    ->helperText(__('resources.role_helper')),
                            ]),

                        Textarea::make('address')
                            ->label(__('resources.address'))
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder(__('resources.address_placeholder'))
                            ->helperText(__('resources.address_helper'))
                            ->columnSpanFull(),
                    ]),

                // Driver License & Documents Section
                Section::make(__('resources.license_documents'))
                    ->description(__('resources.license_documents_description'))
                    ->icon('heroicon-m-identification')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('license_number')
                                    ->label(__('resources.driver_license_number'))
                                    ->maxLength(50)
                                    ->placeholder(__('resources.license_number_placeholder'))
                                    ->suffixIcon('heroicon-m-credit-card')
                                    ->helperText(__('resources.license_required_helper')),

                                Toggle::make('is_verified')
                                    ->label(__('resources.account_verified'))
                                    ->disabled()
                                    ->helperText(__('resources.verification_admin_only')),
                            ]),

                        Grid::make()
                            ->schema([
                                FileUpload::make('id_document_path')
                                    ->label(__('resources.id_document'))
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf'])
                                    ->maxSize(5120)
                                    ->directory('documents/ids')
                                    ->visibility('private')
                                    ->helperText(__('resources.id_document_helper'))
                                    ->downloadable()
                                    ->openable()
                                    ->previewable()
                                    ->imageResizeMode('contain')
                                    ->imageCropAspectRatio('3:2')
                                    ->imageResizeTargetWidth('800')
                                    ->imageResizeTargetHeight('600'),

                                FileUpload::make('license_document_path')
                                    ->label(__('resources.license_document'))
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf'])
                                    ->maxSize(5120)
                                    ->directory('documents/licenses')
                                    ->visibility('private')
                                    ->helperText(__('resources.license_document_helper'))
                                     ->downloadable()
                                    ->openable()
                                    ->previewable(true)
                                    ->imageResizeMode('contain')
                                    ->imageCropAspectRatio('4:3')
                                    ->imageResizeTargetWidth('800')
                                    ->imageResizeTargetHeight('600'),
                            ]),
                    ]),

                // Account Security Section
                Section::make(__('resources.security_settings'))
                    ->description(__('resources.security_settings_description'))
                    ->icon('heroicon-m-lock-closed')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('current_password')
                                    ->label(__('resources.current_password'))
                                    ->password()
                                    ->revealable()
                                    ->maxLength(255)
                                    ->dehydrated(false)
                                    ->requiredWith('password')
                                    ->currentPassword()
                                    ->autocomplete('current-password')
                                    ->suffixIcon('heroicon-m-key')
                                    ->helperText(__('resources.current_password_helper'))
                                    ->validationAttribute('current password'),
                            ]),

                        Grid::make()
                            ->schema([
                                TextInput::make('password')
                                    ->label(__('resources.new_password'))
                                    ->password()
                                    ->revealable()
                                    ->maxLength(255)
                                    ->dehydrated(fn ($state): bool => filled($state))
                                    ->rules([
                                        'nullable',
                                        'string',
                                        'min:8',
                                        'max:255',
                                        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/',
                                    ])
                                    ->autocomplete('new-password')
                                    ->suffixIcon('heroicon-m-lock-closed')
                                    ->helperText('Password must be at least 8 characters with uppercase, lowercase, number, and special character (@$!%*#?&).')
                                    ->validationAttribute('new password')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, $component): void {
                                        // Clear password confirmation when password changes
                                        if (blank($state)) {
                                            $set('password_confirmation', null);
                                        }
                                        // Validate the field
                                    }),

                                TextInput::make('password_confirmation')
                                    ->label(__('resources.confirm_password'))
                                    ->password()
                                    ->revealable()
                                    ->maxLength(255)
                                    ->dehydrated(false)
                                    ->requiredWith('password')
                                    ->same('password')
                                    ->autocomplete('new-password')
                                    ->suffixIcon('heroicon-m-lock-closed')
                                    ->validationAttribute('password confirmation')
                                    ->live(onBlur: true),
                            ]),
                    ]),

                // Account Status Section (Information Only)
                Section::make(__('resources.account_status'))
                    ->description(__('resources.account_status_description'))
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('account_created')
                                    ->label(__('resources.member_since'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-calendar-days'),

                                TextInput::make('last_login_at')
                                    ->label(__('resources.last_login'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-clock'),

                                TextInput::make('password_changed_display')
                                    ->label(__('resources.password_last_changed'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-shield-check'),
                            ]),
                    ]),
            ]);
    }

    /**
     * @throws ValidationException
     */
    public function updateProfile(): void
    {
        try {
            $data = $this->form->getState();
        } catch (ValidationException $e) {
            // Validation failed - Filament will automatically show the errors on the form
            Notification::make()
                ->title('Validation Failed')
                ->body('Please check the form for errors and correct them before saving.')
                ->danger()
                ->send();

            throw $e;
        }

        $user = auth()->user();

        // Check if password is being changed
        $isChangingPassword = isset($data['password']) && filled($data['password']);

        // Remove password fields if not changing password
        if (! $isChangingPassword) {
            unset($data['password'], $data['current_password'], $data['password_confirmation']);
        } else {
            // Password will be automatically hashed by the 'hashed' cast in User model
            // Just set the timestamps and remove confirmation fields
            $data['has_changed_default_password'] = true;
            $data['password_changed_at'] = now();
            unset($data['current_password'], $data['password_confirmation']);
        }

        // Remove preferred_language as it's not a user model field
        if (isset($data['preferred_language'])) {
            session(['locale' => $data['preferred_language']]);
            app()->setLocale($data['preferred_language']);
            unset($data['preferred_language']);
        }

        // Handle file upload arrays - FileUpload returns arrays, but we need strings for database
        if (isset($data['avatar']) && is_array($data['avatar'])) {
            $data['avatar'] = empty($data['avatar']) ? null : $data['avatar'][0];
        }

        if (isset($data['id_document_path']) && is_array($data['id_document_path'])) {
            $data['id_document_path'] = empty($data['id_document_path']) ? null : $data['id_document_path'][0];
        }

        if (isset($data['license_document_path']) && is_array($data['license_document_path'])) {
            $data['license_document_path'] = empty($data['license_document_path']) ? null : $data['license_document_path'][0];
        }

        // Remove display-only fields that aren't part of the user model
        unset($data['account_created'], $data['last_login_at'], $data['password_changed_display']);

        // Log for debugging (only in development)
        if (app()->environment('local')) {
            Log::info('Profile Update Data', [
                'user_id' => $user->id,
                'is_changing_password' => $isChangingPassword,
                'has_password_in_data' => isset($data['password']),
                'data_keys' => array_keys($data),
            ]);
        }

        $user->update($data);

        Notification::make()
            ->title(__('resources.profile_updated_successfully'))
            ->success()
            ->send();

        // If password was changed, show additional notification
        if ($isChangingPassword) {
            Notification::make()
                ->title('Password Changed Successfully')
                ->body('Your password has been updated. Please use your new password for future logins.')
                ->success()
                ->send();
        }
    }

    public function updateProfileAction(): Action
    {
        return Action::make('updateProfile')
            ->label(__('resources.update_profile'))
            ->submit('updateProfile')
            ->color('primary');
    }

    protected function getFormActions(): array
    {
        return [
            $this->updateProfileAction(),
        ];
    }

    //    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If password is being changed, update the timestamp
        if (isset($data['password']) && filled($data['password'])) {
            $data['password_changed_at'] = now();
            $data['has_changed_default_password'] = true;
        }

        return $data;
    }
}

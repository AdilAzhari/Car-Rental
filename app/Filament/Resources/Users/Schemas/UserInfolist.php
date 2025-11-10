<?php

namespace App\Filament\Resources\UserResource\Schemas;

use App\Enums\UserRole;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('resources.personal_information'))
                    ->icon('heroicon-m-user')
                    ->schema([
                        ImageEntry::make('avatar')
                            ->label('Profile Picture')
                            ->circular()
                            ->size(150)
                            ->defaultImageUrl(url('/images/default-avatar.png'))
                            ->visible(fn ($state): bool => ! empty($state))
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('resources.name'))
                                    ->icon('heroicon-m-user')
                                    ->size('lg')
                                    ->weight('bold'),

                                TextEntry::make('email')
                                    ->label(__('resources.email'))
                                    ->copyable()
                                    ->icon('heroicon-m-envelope'),

                                TextEntry::make('phone')
                                    ->label(__('resources.phone'))
                                    ->icon('heroicon-m-phone')
                                    ->placeholder('N/A'),

                                TextEntry::make('date_of_birth')
                                    ->label(__('resources.date_of_birth'))
                                    ->date()
                                    ->icon('heroicon-m-cake')
                                    ->placeholder('N/A'),
                            ]),
                    ]),

                Section::make(__('resources.account_details'))
                    ->icon('heroicon-m-cog-6-tooth')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('role')
                                    ->label(__('resources.user_role'))
                                    ->badge()
                                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                                        'admin' => 'danger',
                                        'owner' => 'warning',
                                        'renter' => 'success',
                                        default => 'gray',
                                    })
                                    ->icon(fn ($state): string => match ($state?->value ?? $state) {
                                        'admin' => 'heroicon-m-shield-check',
                                        'owner' => 'heroicon-m-building-storefront',
                                        'renter' => 'heroicon-m-user',
                                        default => 'heroicon-m-user',
                                    }),

                                TextEntry::make('status')
                                    ->label(__('resources.account_status'))
                                    ->badge()
                                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                                        'active' => 'success',
                                        'approved' => 'info',
                                        'pending' => 'warning',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),

                                IconEntry::make('is_verified')
                                    ->label(__('resources.account_verified'))
                                    ->boolean(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('email_verified_at')
                                    ->label(__('resources.email_verified_at'))
                                    ->dateTime()
                                    ->icon('heroicon-m-check-circle')
                                    ->placeholder('Not verified'),

                                TextEntry::make('created_at')
                                    ->label(__('resources.account_created'))
                                    ->dateTime()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('last_login_at')
                                    ->label('Last Login')
                                    ->dateTime()
                                    ->icon('heroicon-m-arrow-right-on-rectangle')
                                    ->placeholder('Never logged in'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                IconEntry::make('is_new_user')
                                    ->label('New User')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-sparkles')
                                    ->falseIcon('heroicon-o-user'),

                                IconEntry::make('has_changed_default_password')
                                    ->label('Changed Default Password')
                                    ->boolean(),
                            ]),
                    ]),

                Section::make('Password Information')
                    ->icon('heroicon-m-key')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('password')
                                    ->label('Hashed Password')
                                    ->formatStateUsing(fn ($state): string => $state ? substr((string) $state, 0, 20).'...' : 'Not set')
                                    ->copyable()
                                    ->copyableState(fn ($record) => $record->password)
                                    ->copyMessage('Password hash copied!')
                                    ->helperText('Bcrypt hashed password (first 20 chars shown)'),

                                TextEntry::make('password_changed_at')
                                    ->label('Password Last Changed')
                                    ->dateTime()
                                    ->placeholder('Never changed'),
                            ]),
                    ])
                    ->visible(fn (): bool => auth()->user()?->role === UserRole::ADMIN)
                    ->collapsible(),

                Section::make(__('resources.location_information'))
                    ->icon('heroicon-m-map-pin')
                    ->schema([
                        TextEntry::make('address')
                            ->label(__('resources.address'))
                            ->icon('heroicon-m-map-pin')
                            ->placeholder('N/A')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make(__('resources.driver_information'))
                    ->icon('heroicon-m-identification')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('license_number')
                                    ->label(__('resources.license_number'))
                                    ->icon('heroicon-m-credit-card')
                                    ->copyable()
                                    ->placeholder('N/A'),

                                TextEntry::make('license_expiry_date')
                                    ->label(__('resources.license_expiry'))
                                    ->date()
                                    ->icon('heroicon-m-calendar')
                                    ->color(fn ($state): string => $state && $state < now() ? 'danger' : 'success')
                                    ->placeholder('N/A'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('id_document_path')
                                    ->label('ID Document')
                                    ->size(150)
                                    ->visible(fn ($state): bool => ! empty($state)),

                                ImageEntry::make('license_document_path')
                                    ->label('License Document')
                                    ->size(150)
                                    ->visible(fn ($state): bool => ! empty($state)),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Activity & Statistics')
                    ->icon('heroicon-m-chart-bar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('vehicles_count')
                                    ->label('Total Vehicles')
                                    ->getStateUsing(fn ($record): int => $record->vehicles()->count())
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-truck')
                                    ->visible(fn ($record): bool => $record->role?->value === 'owner' || $record->role?->value === 'admin'),

                                TextEntry::make('bookings_count')
                                    ->label('Total Bookings')
                                    ->getStateUsing(fn ($record): int => $record->bookings()->count())
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('reviews_count')
                                    ->label('Total Reviews')
                                    ->getStateUsing(fn ($record): int => $record->reviews()->count())
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-star'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Timestamps')
                    ->icon('heroicon-m-clock')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Account Created')
                                    ->dateTime()
                                    ->icon('heroicon-m-plus-circle')
                                    ->since(),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime()
                                    ->icon('heroicon-m-pencil-square')
                                    ->since(),

                                TextEntry::make('deleted_at')
                                    ->label('Deleted At')
                                    ->dateTime()
                                    ->icon('heroicon-m-trash')
                                    ->visible(fn ($state): bool => ! empty($state))
                                    ->color('danger'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}

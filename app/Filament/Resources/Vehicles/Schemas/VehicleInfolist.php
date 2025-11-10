<?php

namespace App\Filament\Resources\VehicleResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('resources.gallery_images'))
                    ->icon('heroicon-m-photo')
                    ->schema([
                        ImageEntry::make('featured_image')
                            ->label(__('resources.featured_image'))
                            ->size(400)
                            ->square(false)
                            ->extraImgAttributes([
                                'class' => 'rounded-lg shadow-lg transition-all duration-300',
                            ])
                            ->columnSpanFull(),

                        ImageEntry::make('gallery_images')
                            ->label(__('resources.gallery'))
                            ->stacked(false)
                            ->size(150)
                            ->square()
                            ->extraImgAttributes([
                                'class' => 'rounded-lg shadow-md transition-all duration-300',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make(__('resources.basic_information'))
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('make')
                                    ->label(__('resources.make')),

                                TextEntry::make('model')
                                    ->label(__('resources.model')),

                                TextEntry::make('year')
                                    ->label(__('resources.year')),
                            ]),

                        TextEntry::make('plate_number')
                            ->label(__('resources.license_plate'))
                            ->copyable(),
                    ]),

                Section::make(__('resources.vehicle_categories'))
                    ->icon('heroicon-m-tag')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('category')
                                    ->label(__('resources.category'))
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => $state ? ucfirst((string) $state) : 'N/A'),

                                TextEntry::make('transmission')
                                    ->label(__('resources.transmission'))
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => $state?->label() ?? 'N/A'),

                                TextEntry::make('fuel_type')
                                    ->label(__('resources.fuel_type'))
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => $state?->label() ?? 'N/A'),

                                TextEntry::make('seats')
                                    ->label(__('resources.seats'))
                                    ->icon('heroicon-m-user-group')
                                    ->formatStateUsing(fn ($state): string => $state ? $state.' seats' : 'N/A'),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('color')
                                    ->label(__('resources.color'))
                                    ->formatStateUsing(fn ($state): string => $state ? ucfirst((string) $state) : 'N/A'),

                                TextEntry::make('engine_size')
                                    ->label(__('resources.engine_size'))
                                    ->formatStateUsing(fn ($state): string => $state ? $state.' L' : 'N/A'),

                                TextEntry::make('mileage')
                                    ->label(__('resources.mileage_km'))
                                    ->formatStateUsing(fn ($state): string => $state !== null ? number_format($state).' km' : 'N/A'),

                                TextEntry::make('daily_rate')
                                    ->label(__('resources.daily_rate'))
                                    ->money(config('app.currency', 'MYR'))
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),
                            ]),
                    ]),

                Section::make(__('resources.ownership_and_status'))
                    ->icon('heroicon-m-user-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('owner.name')
                                    ->label(__('resources.vehicle_owner'))
                                    ->icon('heroicon-m-user')
                                    ->placeholder('N/A'),

                                TextEntry::make('status')
                                    ->label(__('resources.status'))
                                    ->badge()
                                    ->color(fn ($state) => $state?->color())
                                    ->formatStateUsing(fn ($state): string => $state?->label() ?? 'N/A'),

                                IconEntry::make('is_available')
                                    ->label(__('resources.available_for_rent'))
                                    ->boolean(),
                            ]),
                    ]),

                Section::make('Insurance & Documentation')
                    ->icon('heroicon-m-shield-check')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                IconEntry::make('insurance_included')
                                    ->label(__('resources.insurance_included'))
                                    ->boolean(),

                                TextEntry::make('insurance_expiry')
                                    ->label(__('resources.insurance_expiry'))
                                    ->date()
                                    ->icon('heroicon-m-calendar')
                                    ->color(fn ($state): string => $state && $state < now() ? 'danger' : 'success')
                                    ->placeholder('N/A'),

                                TextEntry::make('insurance_fine_amount')
                                    ->label('Insurance Fine Amount')
                                    ->money('MYR')
                                    ->visible(fn ($state): bool => $state && $state > 0)
                                    ->color('danger')
                                    ->icon('heroicon-m-exclamation-triangle'),
                            ]),

                        ImageEntry::make('documents')
                            ->label(__('resources.documents'))
                            ->stacked(false)
                            ->size(100)
                            ->visible(fn ($state): bool => is_array($state) && count($state) > 0)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('resources.location_information'))
                    ->icon('heroicon-m-map-pin')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('location')
                                    ->label(__('resources.current_location'))
                                    ->icon('heroicon-m-map-pin')
                                    ->formatStateUsing(fn ($state): string => $state ?: 'N/A'),

                                TextEntry::make('pickup_location')
                                    ->label(__('resources.pickup_location'))
                                    ->icon('heroicon-m-map')
                                    ->formatStateUsing(fn ($state): string => $state ?: 'N/A'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('resources.features_specifications'))
                    ->icon('heroicon-m-cog-6-tooth')
                    ->schema([
                        KeyValueEntry::make('features')
                            ->label(__('resources.vehicle_features'))
                            ->columnSpanFull()
                            ->visible(fn ($state): bool => is_array($state) && count($state) > 0),

                        TextEntry::make('description')
                            ->label(__('resources.description'))
                            ->columnSpanFull()
                            ->markdown()
                            ->visible(fn ($state): bool => filled($state)),

                        TextEntry::make('terms_and_conditions')
                            ->label(__('resources.terms_conditions'))
                            ->columnSpanFull()
                            ->visible(fn ($state): bool => filled($state))
                            ->color('warning')
                            ->icon('heroicon-m-document-text'),
                    ])
                    ->collapsible(),

                Section::make(__('resources.traffic_violations'))
                    ->icon('heroicon-m-shield-exclamation')
                    ->description(__('resources.traffic_violations_description'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                IconEntry::make('has_pending_violations')
                                    ->label(__('resources.has_pending_violations'))
                                    ->boolean(),

                                TextEntry::make('total_violations_count')
                                    ->label(__('resources.total_violations_count'))
                                    ->numeric()
                                    ->default(0)
                                    ->icon('heroicon-m-exclamation-circle'),

                                TextEntry::make('total_fines_amount')
                                    ->label(__('resources.total_fines_amount'))
                                    ->money('MYR')
                                    ->default(0)
                                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success'),
                            ]),

                        ViewEntry::make('traffic_violations_display')
                            ->label('')
                            ->view('filament.components.traffic-violations-display')
                            ->visible(fn ($record): bool => is_array($record->traffic_violations) && count($record->traffic_violations) > 0)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Parking & Municipal Violations')
                    ->icon('heroicon-m-building-office-2')
                    ->description('Parking violations from municipal authorities')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                IconEntry::make('has_pending_parking_violations')
                                    ->label('Has Pending Violations')
                                    ->boolean(),

                                TextEntry::make('total_parking_violations_count')
                                    ->label('Total Violations')
                                    ->numeric()
                                    ->default(0)
                                    ->icon('heroicon-m-exclamation-circle'),

                                TextEntry::make('total_parking_fines_amount')
                                    ->label('Total Fines')
                                    ->money('MYR')
                                    ->default(0)
                                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success'),
                            ]),

                        TextEntry::make('parking_violations_last_checked')
                            ->label('Last Checked')
                            ->dateTime()
                            ->icon('heroicon-m-clock')
                            ->visible(fn ($state): bool => filled($state)),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Timestamps')
                    ->icon('heroicon-m-clock')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('resources.created_at'))
                                    ->dateTime()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('updated_at')
                                    ->label(__('resources.updated_at'))
                                    ->dateTime()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('violations_last_checked')
                                    ->label(__('resources.last_checked'))
                                    ->dateTime()
                                    ->icon('heroicon-m-clock')
                                    ->visible(fn ($state): bool => filled($state)),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}

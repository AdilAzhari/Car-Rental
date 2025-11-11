<?php

namespace App\Filament\Resources\ReviewResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Review Overview')
                    ->icon('heroicon-m-star')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('id')
                                    ->label(__('resources.review_id'))
                                    ->formatStateUsing(fn ($state): string => 'RV-'.$state),

                                TextEntry::make('rating')
                                    ->label(__('resources.overall_rating'))
                                    ->formatStateUsing(fn ($state): string => str_repeat('⭐', (int) $state).' ('.$state.'/5)'),

                                TextEntry::make('is_visible')
                                    ->label(__('resources.visibility'))
                                    ->badge()
                                    ->color(fn ($state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn ($state): string => $state ? __('resources.visible') : __('resources.hidden')),
                            ]),
                    ]),

                Section::make('Customer & Booking Details')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('renter.name')
                                    ->label(__('resources.customer_name')),

                                TextEntry::make('renter.email')
                                    ->label(__('resources.customer_email'))
                                    ->copyable(),

                                TextEntry::make('booking.id')
                                    ->label(__('resources.booking_id'))
                                    ->formatStateUsing(fn ($state): string => 'BK-'.$state),

                                TextEntry::make('vehicle_info')
                                    ->label(__('resources.vehicle'))
                                    ->formatStateUsing(fn ($state, $record): string => $record->booking && $record->booking->vehicle ?
                                        "{$record->booking->vehicle->make} {$record->booking->vehicle->model} ({$record->booking->vehicle->year})" :
                                        'N/A'
                                    ),
                            ]),
                    ]),

                Section::make('Review Content')
                    ->icon('heroicon-m-chat-bubble-left-ellipsis')
                    ->schema([
                        TextEntry::make('comment')
                            ->label(__('resources.review_text'))
                            ->columnSpanFull()
                            ->markdown(),
                    ]),

                Section::make('System Information')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Review Submitted')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->label(__('resources.last_updated'))
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}

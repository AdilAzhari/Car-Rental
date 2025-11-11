<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(__('resources.review_information'))
                    ->description(__('resources.customer_feedback'))
                    ->icon('heroicon-m-star')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('booking_id')
                                    ->label(__('resources.booking'))
                                    ->relationship('booking', 'id')
                                    ->searchable()
                                    ->required()
                                    ->getOptionLabelFromRecordUsing(fn ($record): string => "Booking #{$record->id} - {$record->vehicle->make} {$record->vehicle->model}")
                                    ->createOptionForm([
                                        // Booking creation form would go here
                                    ]),

                                Select::make('renter_id')
                                    ->label(__('resources.reviewer'))
                                    ->relationship('renter', 'name')
                                    ->searchable()
                                    ->required()
                                    ->placeholder(__('resources.select_reviewer_placeholder')),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextInput::make('rating')
                                    ->label(__('resources.rating'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(5)
                                    ->step(1)
                                    ->placeholder(__('resources.rating_placeholder'))
                                    ->suffixIcon('heroicon-m-star')
                                    ->helperText(__('resources.rating_helper_text')),
                            ]),
                    ]),

                Section::make(__('resources.review_content'))
                    ->description(__('resources.review_content_description'))
                    ->icon('heroicon-m-chat-bubble-left-ellipsis')
                    ->schema([
                        Textarea::make('comment')
                            ->label(__('resources.comment'))
                            ->required()
                            ->rows(6)
                            ->maxLength(1000)
                            ->placeholder(__('resources.review_comment_placeholder'))
                            ->helperText(__('resources.max_characters', ['count' => 1000]))
                            ->columnSpanFull(),

                        Toggle::make('is_visible')
                            ->label(__('resources.visible_to_public'))
                            ->default(true)
                            ->helperText(__('resources.review_visibility_helper')),
                    ]),

                Select::make('booking_id')
                    ->relationship('booking', 'id')
                    ->required(),
                Select::make('vehicle_id')
                    ->relationship('vehicle', 'id')
                    ->required(),
                Select::make('renter_id')
                    ->relationship('renter', 'name')
                    ->required(),
                TextInput::make('rating')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->columnSpanFull(),
                Toggle::make('is_visible')
                    ->required(),
            ]);
    }
}

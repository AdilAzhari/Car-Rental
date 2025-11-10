<?php

namespace App\Filament\Resources\ActivityLogResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('resources.activity_overview'))
                    ->description(__('resources.activity_overview_description'))
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('id')
                                    ->label(__('resources.log_id'))
                                    ->formatStateUsing(fn ($state): string => '#'.$state),

                                TextEntry::make('log_name')
                                    ->label(__('resources.log_name'))
                                    ->formatStateUsing(fn ($state): string => $state ? ucfirst((string) $state) : __('resources.system')),

                                TextEntry::make('event')
                                    ->label(__('resources.event_type'))
                                    ->formatStateUsing(fn ($state): string => $state ? __(ucfirst((string) $state)) : __('resources.unknown')),
                            ]),

                        TextEntry::make('description')
                            ->label(__('resources.description'))
                            ->formatStateUsing(fn ($state) => $state ?: __('resources.no_description_available'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('resources.subject_information'))
                    ->description(__('resources.subject_information_description'))
                    ->icon('heroicon-m-document')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('subject_type')
                                    ->label(__('resources.subject_type'))
                                    ->formatStateUsing(fn ($state): string => $state ? class_basename($state) : __('resources.none')),

                                TextEntry::make('subject_id')
                                    ->label(__('resources.subject_id'))
                                    ->formatStateUsing(fn ($state): string => $state ? '#'.$state : __('resources.none')),
                            ]),
                    ]),

                Section::make(__('resources.user_information'))
                    ->description(__('resources.user_information_description'))
                    ->icon('heroicon-m-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('causer.name')
                                    ->label(__('resources.performed_by'))
                                    ->formatStateUsing(fn ($state, $record) => $state ?: ($record->causer_type ? __('resources.system') : __('resources.guest'))),

                                TextEntry::make('causer.email')
                                    ->label(__('resources.user_email'))
                                    ->formatStateUsing(fn ($state) => $state ?: __('resources.na')),
                            ]),
                    ]),

                Section::make(__('resources.values_properties'))
                    ->description(__('resources.values_properties_description'))
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        TextEntry::make('properties')
                            ->label(__('resources.properties'))
                            ->formatStateUsing(function ($state, $record): string {
                                if (! $state || (is_object($state) && method_exists($state, 'isEmpty') && $state->isEmpty()) || ($state === [])) {
                                    return __('resources.no_properties_available');
                                }

                                $output = '';
                                $properties = is_array($state) ? $state : $state->toArray();

                                foreach ($properties as $key => $value) {
                                    $output .= "**{$key}:**\n";

                                    if (is_array($value) || is_object($value)) {
                                        $output .= "```json\n".json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n```\n\n";
                                    } elseif (is_bool($value)) {
                                        $output .= ($value ? 'true' : 'false')."\n\n";
                                    } elseif (is_null($value)) {
                                        $output .= "null\n\n";
                                    } else {
                                        $output .= $value."\n\n";
                                    }
                                }

                                return $output;
                            })
                            ->markdown()
                            ->columnSpanFull()
                            ->visible(fn ($record): bool => $record->properties && (
                                (is_object($record->properties) && method_exists($record->properties, 'isNotEmpty') && $record->properties->isNotEmpty()) ||
                                (is_array($record->properties) && $record->properties !== [])
                            )),

                        TextEntry::make('properties')
                            ->label(__('resources.no_properties'))
                            ->formatStateUsing(fn (): string => __('resources.no_properties_available'))
                            ->visible(fn ($record): bool => ! $record->properties || (
                                (is_object($record->properties) && method_exists($record->properties, 'isEmpty') && $record->properties->isEmpty()) ||
                                (is_array($record->properties) && $record->properties === [])
                            ))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('resources.system_information'))
                    ->icon('heroicon-m-clock')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('resources.occurred_at'))
                                    ->dateTime(),

                                TextEntry::make('batch_uuid')
                                    ->label(__('resources.batch_id'))
                                    ->formatStateUsing(fn ($state): string => $state ? substr((string) $state, 0, 8).'...' : __('resources.none'))
                                    ->copyable()
                                    ->tooltip(__('resources.click_to_copy_full_uuid')),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}

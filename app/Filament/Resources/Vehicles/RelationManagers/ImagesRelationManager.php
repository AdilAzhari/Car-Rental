<?php

namespace App\Filament\Resources\Vehicles\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.vehicle_image'))
                    ->schema([
                        Grid::make()
                            ->columns(1)
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label(__('resources.image'))
                                    ->required()
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->directory('vehicle-images')
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                                    ->helperText(__('resources.vehicle_image_helper'))
                                    ->columnSpanFull(),

                                TextInput::make('caption')
                                    ->label(__('resources.caption'))
                                    ->maxLength(255)
                                    ->placeholder(__('resources.image_caption_placeholder'))
                                    ->helperText(__('resources.optional')),

                                Toggle::make('is_primary')
                                    ->label(__('resources.primary_image'))
                                    ->helperText(__('resources.primary_image_helper'))
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label(__('resources.image'))
                    ->square()
                    ->size(80),

                TextColumn::make('caption')
                    ->label(__('resources.caption'))
                    ->limit(30)
                    ->placeholder(__('resources.no_caption'))
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                IconColumn::make('is_primary')
                    ->label(__('resources.primary'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label(__('resources.uploaded'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(__('resources.upload_vehicle_image'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['vehicle_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('resources.edit_image')),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('display_order')
            ->defaultSort('display_order', 'asc');
    }
}

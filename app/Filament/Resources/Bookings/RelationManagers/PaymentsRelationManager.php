<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Schemas\PaymentForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('resources.payment_id'))
                    ->formatStateUsing(fn ($state): string => 'PAY-'.$state)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label(__('resources.amount'))
                    ->money(config('app.currency', 'MYR'))
                    ->sortable(),

                BadgeColumn::make('payment_method')
                    ->label(__('resources.payment_method'))
                    ->getStateUsing(fn ($record): string => $record->payment_method instanceof PaymentMethod ? $record->payment_method->label() : (string) $record->payment_method)
                    ->colors([
                        'success' => 'credit_card',
                        'info' => 'bank_transfer',
                        'warning' => 'cash',
                        'secondary' => 'online_banking',
                    ]),

                BadgeColumn::make('payment_status')
                    ->label(__('resources.status'))
                    ->getStateUsing(fn ($record): string => $record->payment_status instanceof PaymentStatus ? $record->payment_status->label() : (string) $record->payment_status)
                    ->colors([
                        'warning' => 'unpaid',
                        'success' => 'paid',
                        'secondary' => 'refunded',
                    ])
                    ->sortable(),

                TextColumn::make('transaction_id')
                    ->label(__('resources.transaction_id'))
                    ->searchable()
                    ->placeholder(__('resources.no_transaction_id'))
                    ->copyable()
                    ->copyMessage(__('resources.copied'))
                    ->limit(20),

                TextColumn::make('paid_at')
                    ->label(__('resources.paid_at'))
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder(__('resources.not_paid_yet')),

                TextColumn::make('created_at')
                    ->label(__('resources.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label(__('resources.status'))
                    ->options(PaymentStatus::class),

                SelectFilter::make('payment_method')
                    ->label(__('resources.payment_method'))
                    ->options(PaymentMethod::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(__('resources.record_payment'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['booking_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn ($record): string => __('resources.payment').' #PAY-'.$record->id)
                    ->infolist(function (): array {
                        return [
                            Section::make(__('resources.payment_details'))
                                ->schema([
                                    Grid::make()
                                        ->columns()
                                        ->schema([
                                            TextEntry::make('id')
                                                ->label(__('resources.payment_id'))
                                                ->formatStateUsing(fn ($state): string => 'PAY-'.$state),

                                            TextEntry::make('amount')
                                                ->label(__('resources.amount'))
                                                ->money(config('app.currency', 'MYR')),

                                            TextEntry::make('payment_method')
                                                ->label(__('resources.payment_method'))
                                                ->formatStateUsing(fn ($state): string => $state instanceof PaymentMethod ? $state->label() : (string) $state)
                                                ->badge(),

                                            TextEntry::make('payment_status')
                                                ->label(__('resources.status'))
                                                ->formatStateUsing(fn ($state): string => $state instanceof PaymentStatus ? $state->label() : (string) $state)
                                                ->badge(),

                                            TextEntry::make('transaction_id')
                                                ->label(__('resources.transaction_id'))
                                                ->placeholder(__('resources.no_transaction_id'))
                                                ->copyable(),

                                            TextEntry::make('paid_at')
                                                ->label(__('resources.paid_at'))
                                                ->dateTime()
                                                ->placeholder(__('resources.not_paid_yet')),

                                            TextEntry::make('notes')
                                                ->label(__('resources.notes'))
                                                ->placeholder(__('resources.no_notes'))
                                                ->columnSpanFull(),

                                            TextEntry::make('created_at')
                                                ->label(__('resources.created'))
                                                ->dateTime(),

                                            TextEntry::make('updated_at')
                                                ->label(__('resources.updated'))
                                                ->dateTime(),
                                        ]),
                                ]),
                        ];
                    }),
                EditAction::make()
                    ->modalHeading(fn ($record): string => __('resources.edit_payment').' #PAY-'.$record->id),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

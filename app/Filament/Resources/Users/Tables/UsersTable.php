<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\Schemas\UserInfolist;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\FilamentQueryOptimizationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ImageColumn;
class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
          ->columns([
                ImageColumn::make('avatar')
                    ->imageHeight(40)
                    ->defaultImageUrl(url('/images/User-placeholder.jpg'))
                    ->visibleFrom('md')
                    ->circular(),
                    
                TextColumn::make('name')
                    ->label(__('resources.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->summarize(Count::make()->label('Total Users')),

                TextColumn::make('email')
                    ->label(__('resources.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                TextColumn::make('role')
                    ->label(__('resources.role'))
                    ->getStateUsing(fn ($record) => $record->role instanceof UserRole ? $record->role->value : (string) $record->role)
                    ->formatStateUsing(fn ($state): string => (string) $state)
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'admin' => 'danger',
                        'owner' => 'warning',
                        'renter' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn ($state): string => match ($state) {
                        'admin' => 'heroicon-m-shield-check',
                        'owner' => 'heroicon-m-building-storefront',
                        'renter' => 'heroicon-m-user',
                        default => 'heroicon-m-user',
                    }),

                BooleanColumn::make('is_verified')
                    ->label(__('resources.verified'))
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('status')
                    ->label(__('resources.status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof UserStatus ? $state->value : $state) {
                        'active' => 'success',
                        'approved' => 'info',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state): string => match ($state instanceof UserStatus ? $state->value : $state) {
                        'active' => __('enums.user_status.active'),
                        'approved' => __('enums.user_status.approved'),
                        'pending' => __('enums.user_status.pending'),
                        'rejected' => __('enums.user_status.rejected'),
                        default => (string) $state,
                    }),

                TextColumn::make('phone')
                    ->label(__('resources.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('address')
                    ->label(__('resources.address'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),

                TextColumn::make('bookings_count')
                    ->label(__('resources.bookings'))
                    ->counts('bookings')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label(__('resources.joined'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('resources.role'))
                    ->options([
                        'admin' => __('enums.user_role.admin'),
                        'owner' => __('enums.user_role.owner'),
                        'renter' => __('enums.user_role.customer'),
                    ]),

                SelectFilter::make('is_verified')
                    ->label(__('resources.verification_status'))
                    ->options([
                        '1' => __('resources.verified'),
                        '0' => __('resources.unverified'),
                    ]),

                SelectFilter::make('status')
                    ->label(__('resources.account_status'))
                    ->options([
                        'active' => __('enums.user_status.active'),
                        'approved' => __('enums.user_status.approved'),
                        'pending' => __('enums.user_status.pending'),
                        'rejected' => __('enums.user_status.rejected'),
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('resources.joined_from')),
                        DatePicker::make('created_until')
                            ->label(__('resources.joined_until')),
                    ])
                    ->query(fn (Builder $builder, array $data): Builder => $builder
                        ->when(
                            $data['created_from'],
                            fn (Builder $builder, $date): Builder => $builder->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $builder, $date): Builder => $builder->whereDate('created_at', '<=', $date),
                        )),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label(__('widgets.export'))
                    ->color('success')
                    ->icon('heroicon-m-arrow-down-tray'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn ($record): string => __('resources.user').': '.$record->name)
                    ->infolist(fn (): array => UserInfolist::configure(new Schema)->getComponents()),
                EditAction::make(),
                Action::make('changePassword')
                    ->label('Change Password')
                    ->icon('heroicon-m-key')
                    ->color('warning')
                    ->form([
                        TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText('Minimum 8 characters')
                            ->placeholder('Enter new password'),
                        TextInput::make('confirm_password')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->same('new_password')
                            ->placeholder('Re-enter password'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'password' => bcrypt($data['new_password']),
                            'password_changed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Password Changed')
                            ->body("Password for {$record->name} has been updated successfully.")
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn ($record): string => "Change Password for {$record->name}")
                    ->modalDescription('Set a new password for this user account.')
                    ->modalSubmitActionLabel('Change Password')
                    ->visible(fn (): bool => auth()->user()?->role === UserRole::ADMIN),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    FilamentExportBulkAction::make('bulk_export')
                        ->label(__('widgets.export'))
                        ->icon('heroicon-m-arrow-down-tray'),
                ]),
            ]);
    }
}

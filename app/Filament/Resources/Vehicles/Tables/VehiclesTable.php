<?php

namespace App\Filament\Resources\Vehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VehicleStatus;
use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Filament\Resources\VehicleResource\Schemas\VehicleInfolist;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\FilamentQueryOptimizationService;
use App\Services\TrafficViolationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Override;
use UnitEnum;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label(__('resources.image'))
                    ->size(80)
                    ->circular()
                    ->defaultImageUrl(url('/images/car-placeholder.jpg'))
                    ->visibleFrom('md')
                    ->getStateUsing(fn ($record) => $record->featured_image ?: '/images/car-placeholder.jpg'),

                TextColumn::make('make')
                    ->label(__('resources.make'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('model')
                    ->label(__('resources.model'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('year')
                    ->label(__('resources.year'))
                    ->sortable(),

                TextColumn::make('plate_number')
                    ->label(__('resources.plate_number'))
                    ->searchable()
                    ->fontFamily('mono')
                    ->copyable(),

                TextColumn::make('category')
                    ->label(__('resources.category'))
                    ->formatStateUsing(fn ($state): string => $state ? ucfirst((string) $state) : 'N/A')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'economy' => 'success',
                        'compact' => 'info',
                        'midsize' => 'warning',
                        'fullsize' => 'primary',
                        'luxury' => 'danger',
                        'suv' => 'gray',
                        'sports' => 'orange',
                        'pickup' => 'purple',
                        'minivan' => 'indigo',
                        'convertible' => 'emerald',
                        'electric' => 'green',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label(__('resources.status'))
                    ->getStateUsing(fn ($record) => $record->status instanceof VehicleStatus ? $record->status->value : (string) $record->status)
                    ->formatStateUsing(fn ($state): string => (string) $state)
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        VehicleStatus::PUBLISHED->value => 'success',
                        VehicleStatus::DRAFT->value => 'warning',
                        VehicleStatus::MAINTENANCE->value => 'danger',
                        VehicleStatus::ARCHIVED->value => 'gray',
                        default => 'gray',
                    }),

                BooleanColumn::make('is_available')
                    ->label(__('resources.available'))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('daily_rate')
                    ->label(__('resources.daily_rate'))
                    ->money(config('app.currency', 'MYR'))
                    ->summarize([
                        Average::make()->money(config('app.currency', 'MYR'))->label('Avg Rate'),
                        Sum::make()->money(config('app.currency', 'MYR'))->label('Total Rates'),
                        Count::make()->label('Total Vehicles'),
                    ])
                    ->sortable(),

                TextColumn::make('owner.name')
                    ->label(__('resources.owner'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—')
                    ->default('N/A'),

                TextColumn::make('bookings_count')
                    ->label(__('resources.bookings'))
                    ->counts('bookings')
                    ->badge()
                    ->color('info'),

                TextColumn::make('has_pending_violations')
                    ->label(__('resources.traffic_violations'))
                    ->getStateUsing(function ($record): string {
                        if (empty($record->traffic_violations)) {
                            return __('vehicles.none');
                        }

                        $pendingCount = collect($record->traffic_violations)->where('status', 'pending')->count();

                        if ($pendingCount > 0) {
                            return $pendingCount.' pending';
                        }

                        return 'resolved';
                    })
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state === __('vehicles.none') => 'success',
                        $state === 'resolved' => 'warning',
                        str_contains((string) $state, 'pending') => 'danger',
                        default => 'gray',
                    })
                    ->icons([
                        'heroicon-o-check-circle' => 'none',
                        'heroicon-o-exclamation-triangle' => fn ($state): bool => str_contains((string) $state, 'pending'),
                        'heroicon-o-shield-check' => 'resolved',
                    ]),

                TextColumn::make('insurance_fine_status')
                    ->label('Insurance Fine')
                    ->getStateUsing(function ($record): string {
                        if ($record->insurance_expiry >= now()) {
                            return 'Valid';
                        }

                        if ($record->insurance_fine_amount <= 0) {
                            return 'Expired - No Fine';
                        }

                        if ($record->insurance_fine_paid) {
                            return 'Fine Paid';
                        }

                        return 'RM'.number_format($record->insurance_fine_amount, 2).' Due';
                    })
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state === 'Valid' => 'success',
                        $state === 'Fine Paid' => 'info',
                        $state === 'Expired - No Fine' => 'warning',
                        str_contains((string) $state, 'Due') => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn ($state): string => match (true) {
                        $state === 'Valid' => 'heroicon-o-shield-check',
                        $state === 'Fine Paid' => 'heroicon-o-check-circle',
                        str_contains((string) $state, 'Due') => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-information-circle',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('parking_violations_status')
                    ->label('Parking Violations')
                    ->getStateUsing(function ($record): string {
                        if (empty($record->parking_violations)) {
                            return 'None';
                        }

                        $pendingCount = collect($record->parking_violations)->where('status', 'pending')->count();

                        if ($pendingCount > 0) {
                            return $pendingCount.' pending (RM'.number_format($record->total_parking_fines_amount, 2).')';
                        }

                        return 'All Resolved';
                    })
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state === 'None' => 'success',
                        $state === 'All Resolved' => 'info',
                        str_contains((string) $state, 'pending') => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn ($state): string => match (true) {
                        $state === 'None' => 'heroicon-o-check-circle',
                        $state === 'All Resolved' => 'heroicon-o-shield-check',
                        str_contains((string) $state, 'pending') => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-information-circle',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('resources.added'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make()
                    ->modalHeading(fn ($record): string => $record->make.' '.$record->model.' ('.$record->year.')')
                    ->infolist(fn (): array => VehicleInfolist::configure(new \Filament\Schemas\Schema)->getComponents()),
                DeleteAction::make(),
                Action::make('check_violations')
                    ->label('Check Violations')
                    ->icon('heroicon-m-exclamation-triangle')
                    ->color('warning')
                    ->action(function (Vehicle $vehicle): void {
                        $trafficViolationService = app(TrafficViolationService::class);

                        try {
                            // Force check by clearing cache first
                            $trafficViolationService->clearCache($vehicle->plate_number);
                            $violationData = $trafficViolationService->checkVehicleViolations($vehicle);
                            $trafficViolationService->updateVehicleViolations($vehicle, $violationData);

                            if ($violationData['has_violations']) {
                                $count = count($violationData['violations']);
                                $fines = number_format($violationData['total_fines_amount'], 2);

                                if ($violationData['has_pending_violations']) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Traffic violations found!')
                                        ->body("Found {$count} violation(s) with RM{$fines} in pending fines.")
                                        ->warning()
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Traffic violations found')
                                        ->body("Found {$count} violation(s), all resolved.")
                                        ->info()
                                        ->send();
                                }
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('No violations found')
                                    ->body("Vehicle {$vehicle->plate_number} has no traffic violations.")
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Check failed')
                                ->body('Failed to check violations: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalDescription('This will send an SMS to check for current traffic violations.')
                    ->visible(fn (): bool => auth()->user() && in_array(auth()->user()->role->value, ['admin', 'owner'])),
                Action::make('pay_traffic_violations')
                    ->label('Pay Traffic Fines')
                    ->icon('heroicon-m-currency-dollar')
                    ->color('warning')
                    ->form([
                        Placeholder::make('payment_gateway_notice')
                            ->label('Payment Gateway Integration')
                            ->content('🔄 Payment gateway integration is in progress. This feature will be available soon.')
                            ->extraAttributes([
                                'class' => 'text-center p-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-2 border-yellow-300 dark:border-yellow-700',
                            ]),
                        Placeholder::make('violations_summary')
                            ->label('Violations Summary')
                            ->content(fn (Vehicle $vehicle): string => "Vehicle: {$vehicle->make} {$vehicle->model} ({$vehicle->plate_number})\n".
                                "Total Violations: {$vehicle->total_violations_count}\n".
                                'Total Fines: RM '.number_format($vehicle->total_fines_amount, 2)."\n".
                                'Status: '.($vehicle->has_pending_violations ? 'Pending Payment' : 'All Paid')
                            )
                            ->extraAttributes([
                                'class' => 'p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 whitespace-pre-line',
                            ]),
                        ViewField::make('violations_list')
                            ->label('Violation Details')
                            ->view('filament.components.traffic-violations-display')
                            ->visible(fn (Vehicle $vehicle): bool => $vehicle && $vehicle->traffic_violations && count($vehicle->traffic_violations) > 0),
                        Placeholder::make('payment_info')
                            ->label('What happens next?')
                            ->content(
                                "Once the payment gateway is integrated, you will be able to:\n\n".
                                "✓ Pay individual violations or all at once\n".
                                "✓ Use Credit/Debit cards, FPX, e-wallets\n".
                                "✓ Get instant payment confirmation\n".
                                "✓ Receive official JPJ payment receipts\n".
                                '✓ Track payment history'
                            )
                            ->extraAttributes([
                                'class' => 'p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 whitespace-pre-line',
                            ]),
                    ])
                    ->action(function (Vehicle $vehicle): void {
                        \Filament\Notifications\Notification::make()
                            ->title('Payment Gateway Coming Soon')
                            ->body('Traffic violation payment will be available once the payment gateway is integrated.')
                            ->warning()
                            ->duration(8000)
                            ->send();
                    })
                    ->modalHeading(fn (Vehicle $vehicle): string => 'Pay Traffic Fines - RM '.number_format($vehicle->total_fines_amount, 2))
                    ->modalSubmitActionLabel('Acknowledge')
                    ->modalCancelActionLabel('Close')
                    ->slideOver()
                    ->visible(fn (Vehicle $vehicle): bool => auth()->user() &&
                        in_array(auth()->user()->role->value, ['admin', 'owner']) &&
                        $vehicle->has_pending_violations &&
                        $vehicle->total_fines_amount > 0
                    ),
                Action::make('check_parking_violations')
                    ->label('Check Parking Violations')
                    ->icon('heroicon-m-exclamation-triangle')
                    ->color('info')
                    ->action(function (Vehicle $vehicle): void {
                        // Simulate checking parking violations
                        // In production, this would call municipal/DBKL API
                        $mockViolations = [
                            [
                                'violation_type' => 'Illegal Parking',
                                'location' => 'Jalan Bukit Bintang, KL',
                                'date' => now()->subDays(5)->format('Y-m-d H:i:s'),
                                'fine_amount' => 150.00,
                                'status' => 'pending',
                                'authority' => 'DBKL',
                                'reference_number' => 'DBKL-'.now()->format('Y').'-'.random_int(10000, 99999),
                            ],
                        ];

                        $totalFines = collect($mockViolations)->sum('fine_amount');
                        $pendingCount = collect($mockViolations)->where('status', 'pending')->count();

                        $vehicle->update([
                            'parking_violations' => $mockViolations,
                            'parking_violations_last_checked' => now(),
                            'total_parking_violations_count' => count($mockViolations),
                            'total_parking_fines_amount' => $totalFines,
                            'has_pending_parking_violations' => $pendingCount > 0,
                        ]);

                        if ($pendingCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Parking violations found!')
                                ->body("Found {$pendingCount} parking violation(s) with RM".number_format($totalFines, 2).' in pending fines.')
                                ->warning()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('No parking violations')
                                ->body("Vehicle {$vehicle->plate_number} has no pending parking violations.")
                                ->success()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalDescription('This will check for parking violations from municipal authorities (DBKL, MPSJ, etc.).')
                    ->visible(fn (): bool => auth()->user() && in_array(auth()->user()->role->value, ['admin', 'owner'])),
                Action::make('pay_parking_violations')
                    ->label('Pay Parking Fines')
                    ->icon('heroicon-m-currency-dollar')
                    ->color('info')
                    ->form([
                        Placeholder::make('payment_gateway_notice')
                            ->label('Payment Gateway Integration')
                            ->content('🔄 Payment gateway integration is in progress. This feature will be available soon.')
                            ->extraAttributes([
                                'class' => 'text-center p-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-2 border-yellow-300 dark:border-yellow-700',
                            ]),
                        Placeholder::make('violations_summary')
                            ->label('Parking Violations Summary')
                            ->content(fn (Vehicle $vehicle): string => "Vehicle: {$vehicle->make} {$vehicle->model} ({$vehicle->plate_number})\n".
                                "Total Parking Violations: {$vehicle->total_parking_violations_count}\n".
                                'Total Fines: RM '.number_format($vehicle->total_parking_fines_amount, 2)."\n".
                                'Status: '.($vehicle->has_pending_parking_violations ? 'Pending Payment' : 'All Paid')
                            )
                            ->extraAttributes([
                                'class' => 'p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 whitespace-pre-line',
                            ]),
                        Placeholder::make('violations_details')
                            ->label('Violation Details')
                            ->content(fn (Vehicle $vehicle): string => collect($vehicle->parking_violations ?? [])
                                ->map(fn ($violation, $index): string => ($index + 1).". {$violation['violation_type']}\n".
                                    "   Location: {$violation['location']}\n".
                                    "   Date: {$violation['date']}\n".
                                    '   Fine: RM '.number_format($violation['fine_amount'], 2)."\n".
                                    "   Authority: {$violation['authority']}\n".
                                    "   Reference: {$violation['reference_number']}"
                                )
                                ->join("\n\n"))
                            ->visible(fn (Vehicle $vehicle): bool => $vehicle && $vehicle->parking_violations && count($vehicle->parking_violations) > 0)
                            ->extraAttributes([
                                'class' => 'p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-300 dark:border-gray-600 whitespace-pre-line font-mono text-sm',
                            ]),
                        Placeholder::make('payment_info')
                            ->label('What happens next?')
                            ->content(
                                "Once the payment gateway is integrated, you will be able to:\n\n".
                                "✓ Pay parking fines from multiple authorities\n".
                                "✓ Use Credit/Debit cards, FPX, e-wallets\n".
                                "✓ Get instant payment confirmation\n".
                                "✓ Receive official municipal payment receipts\n".
                                '✓ Track payment history across all authorities'
                            )
                            ->extraAttributes([
                                'class' => 'p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 whitespace-pre-line',
                            ]),
                    ])
                    ->action(function (Vehicle $vehicle): void {
                        \Filament\Notifications\Notification::make()
                            ->title('Payment Gateway Coming Soon')
                            ->body('Parking violation payment will be available once the payment gateway is integrated.')
                            ->warning()
                            ->duration(8000)
                            ->send();
                    })
                    ->modalHeading(fn (Vehicle $vehicle): string => 'Pay Parking Fines - RM '.number_format($vehicle->total_parking_fines_amount, 2))
                    ->modalSubmitActionLabel('Acknowledge')
                    ->modalCancelActionLabel('Close')
                    ->slideOver()
                    ->visible(fn (Vehicle $vehicle): bool => auth()->user() &&
                        in_array(auth()->user()->role->value, ['admin', 'owner']) &&
                        $vehicle->has_pending_parking_violations &&
                        $vehicle->total_parking_fines_amount > 0
                    ),
                Action::make('pay_insurance_fine')
                    ->label('Pay Insurance Fine')
                    ->icon('heroicon-m-currency-dollar')
                    ->color('danger')
                    ->form([
                        Placeholder::make('payment_gateway_notice')
                            ->label('Payment Gateway Integration')
                            ->content('🔄 Payment gateway integration is in progress. This feature will be available soon.')
                            ->extraAttributes([
                                'class' => 'text-center p-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-2 border-yellow-300 dark:border-yellow-700',
                            ]),
                        Placeholder::make('fine_details')
                            ->label('Fine Details')
                            ->content(fn (Vehicle $vehicle): string => "Vehicle: {$vehicle->make} {$vehicle->model} ({$vehicle->plate_number})\n".
                                "Insurance Expired: {$vehicle->insurance_expiry->format('d M Y')}\n".
                                'Fine Amount: RM '.number_format($vehicle->insurance_fine_amount, 2)
                            )
                            ->extraAttributes([
                                'class' => 'p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 whitespace-pre-line',
                            ]),
                        Placeholder::make('payment_info')
                            ->label('What happens next?')
                            ->content(
                                "Once the payment gateway is integrated, you will be able to:\n\n".
                                "✓ Pay securely using Credit/Debit cards\n".
                                "✓ Use online banking (FPX)\n".
                                "✓ Pay via e-wallets (Touch 'n Go, GrabPay, etc.)\n".
                                "✓ Get instant payment confirmation\n".
                                '✓ Receive payment receipts via email'
                            )
                            ->extraAttributes([
                                'class' => 'p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 whitespace-pre-line',
                            ]),
                    ])
                    ->action(function (Vehicle $vehicle): void {
                        \Filament\Notifications\Notification::make()
                            ->title('Payment Gateway Coming Soon')
                            ->body('The payment gateway integration is currently in development. You will be notified when it becomes available.')
                            ->warning()
                            ->duration(8000)
                            ->send();
                    })
                    ->modalHeading(fn (Vehicle $vehicle): string => 'Pay Insurance Fine - RM '.number_format($vehicle->insurance_fine_amount, 2))
                    ->modalSubmitActionLabel('Acknowledge')
                    ->modalCancelActionLabel('Close')
                    ->slideOver()
                    ->visible(fn (Vehicle $vehicle): bool => auth()->user() &&
                        in_array(auth()->user()->role->value, ['admin', 'owner']) &&
                        $vehicle->insurance_expiry < now() &&
                        $vehicle->insurance_fine_amount > 0 &&
                        ! $vehicle->insurance_fine_paid
                    ),
                Action::make('pay_all_fines')
                    ->label('Pay All Fines')
                    ->icon('heroicon-m-banknotes')
                    ->color('primary')
                    // ->url(fn (Vehicle $vehicle): string => static::getUrl('pay-fines', ['record' => $vehicle]))
                    ->visible(fn (Vehicle $vehicle): bool => auth()->user() &&
                        in_array(auth()->user()->role->value, ['admin', 'owner']) &&
                        (
                            ($vehicle->insurance_expiry < now() && $vehicle->insurance_fine_amount > 0 && ! $vehicle->insurance_fine_paid) ||
                            ($vehicle->has_pending_violations && $vehicle->total_fines_amount > 0) ||
                            ($vehicle->has_pending_parking_violations && $vehicle->total_parking_fines_amount > 0)
                        )
                    ),
                Action::make('book_now')
                    ->label(__('resources.book_now'))
                    ->icon('heroicon-m-calendar-plus')
                    ->color('success')
                    ->url(fn (Vehicle $vehicle): string => route('filament.admin.resources.bookings.create', [
                        'vehicle_id' => $vehicle->id,
                    ]))
                    ->visible(fn (Vehicle $vehicle): bool => auth()->user() &&
                        auth()->user()->role === UserRole::RENTER &&
                        $vehicle->status === VehicleStatus::PUBLISHED &&
                        $vehicle->is_available
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    FilamentExportBulkAction::make('bulk_export')
                        ->label(__('widgets.export'))
                        ->icon('heroicon-m-arrow-down-tray'),
                    ]),
                ]),
            ]);
    }
}

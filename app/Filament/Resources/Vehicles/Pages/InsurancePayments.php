<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\Vehicles\VehicleResource as VehiclesVehicleResource;
use App\Models\Vehicle;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;

class InsurancePayments extends Page
{
    protected static string $resource = VehiclesVehicleResource::class;

    protected string $view = 'filament.resources.vehicle-resource.pages.insurance-payments';

    public Vehicle $record;

    public function mount(Vehicle $vehicle): void
    {
        $this->record = $vehicle;
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canView($this->record), 403);
    }

    #[\Override]
    public function getTitle(): string
    {
        return 'Insurance & Payments - '.$this->record->plate_number;
    }

    #[\Override]
    public function getSubheading(): ?string
    {
        return $this->record->make.' '.$this->record->model.' ('.$this->record->year.')';
    }

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('renew_insurance')
                ->label('Renew Insurance')
                ->color('success')
                ->form([
                    Placeholder::make('payment_gateway_notice')
                        ->label('Payment Gateway Integration')
                        ->content('Payment gateway integration is in progress. This feature will be available soon.')
                        ->extraAttributes([
                            'class' => 'text-center p-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-2 border-yellow-300 dark:border-yellow-700',
                        ]),

                    Placeholder::make('current_info')
                        ->label('Current Insurance Information')
                        ->content(fn (): string => $this->record->insurance_expiry
                            ? "Current Expiry: {$this->record->insurance_expiry->format('d M Y')}\n".
                                'Status: '.($this->record->insurance_expiry < now() ? 'Expired' : 'Active')
                            : "Current Expiry: Not Set\nStatus: No Insurance"
                        )
                        ->extraAttributes([
                            'class' => 'p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 whitespace-pre-line',
                        ]),

                    Select::make('insurance_type')
                        ->label('Insurance Type')
                        ->options([
                            'comprehensive' => 'Comprehensive',
                            'third_party' => 'Third Party',
                            'third_party_fire_theft' => 'Third Party, Fire & Theft',
                        ])
                        ->default('comprehensive')
                        ->required()
                        ->native(false),

                    DatePicker::make('new_expiry_date')
                        ->label('New Expiry Date')
                        ->default(now()->addYear())
                        ->minDate(now())
                        ->required(),

                    TextInput::make('premium_amount')
                        ->label('Premium Amount')
                        ->prefix('RM')
                        ->numeric()
                        ->default(1200.00)
                        ->required(),

                    FileUpload::make('insurance_documents')
                        ->label('Upload Insurance Documents (Optional)')
                        ->multiple()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxFiles(3)
                        ->helperText('Upload your insurance policy documents for record keeping'),

                    Placeholder::make('payment_info')
                        ->label('What happens next?')
                        ->content(
                            "Once the payment gateway is integrated, you will be able to:\n\n".
                            "- Pay insurance premium securely online\n".
                            "- Use Credit/Debit cards, FPX, e-wallets\n".
                            "- Get instant confirmation\n".
                            "- Receive policy documents via email\n".
                            '- Automatic expiry reminders'
                        )
                        ->extraAttributes([
                            'class' => 'p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 whitespace-pre-line',
                        ]),
                ])
                ->action(function (): void {
                    \Filament\Notifications\Notification::make()
                        ->title('Payment Gateway Coming Soon')
                        ->body('Insurance renewal payment will be available once the payment gateway is integrated.')
                        ->warning()
                        ->duration(8000)
                        ->send();
                })
                ->modalHeading('Renew Vehicle Insurance')
                ->modalSubmitActionLabel('Acknowledge')
                ->modalCancelActionLabel('Close')
                ->slideOver(),

            PageAction::make('pay_fine')
                ->label('Pay Fine')
                ->color('danger')
                ->visible(fn (): bool => $this->record->insurance_expiry < now() &&
                    $this->record->insurance_fine_amount > 0 &&
                    ! $this->record->insurance_fine_paid
                )
                ->form([
                    Placeholder::make('payment_gateway_notice')
                        ->label('Payment Gateway Integration')
                        ->content('Payment gateway integration is in progress. This feature will be available soon.')
                        ->extraAttributes([
                            'class' => 'text-center p-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-2 border-yellow-300 dark:border-yellow-700',
                        ]),

                    Placeholder::make('fine_details')
                        ->label('Fine Details')
                        ->content(fn (): string => "Vehicle: {$this->record->make} {$this->record->model} ({$this->record->plate_number})\n".
                            "Insurance Expired: {$this->record->insurance_expiry->format('d M Y')}\n".
                            'Fine Amount: RM '.number_format($this->record->insurance_fine_amount, 2)
                        )
                        ->extraAttributes([
                            'class' => 'p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 whitespace-pre-line',
                        ]),
                ])
                ->action(function (): void {
                    \Filament\Notifications\Notification::make()
                        ->title('Payment Gateway Coming Soon')
                        ->body('Insurance fine payment will be available once the payment gateway is integrated.')
                        ->warning()
                        ->duration(8000)
                        ->send();
                })
                ->modalHeading(fn (): string => 'Pay Insurance Fine - RM '.number_format($this->record->insurance_fine_amount, 2))
                ->modalSubmitActionLabel('Acknowledge')
                ->modalCancelActionLabel('Close')
                ->slideOver(),
        ];
    }

    public function isInsuranceExpired(): bool
    {
        if (! $this->record->insurance_expiry) {
            return false;
        }

        return $this->record->insurance_expiry < now();
    }

    public function getDaysUntilExpiry(): int
    {
        if (! $this->record->insurance_expiry) {
            return 0;
        }

        return now()->diffInDays($this->record->insurance_expiry, false);
    }

    public function hasFine(): bool
    {
        if (! $this->record->insurance_expiry) {
            return false;
        }

        return $this->record->insurance_expiry < now() &&
               $this->record->insurance_fine_amount > 0 &&
               ! $this->record->insurance_fine_paid;
    }
}

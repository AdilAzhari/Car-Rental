<?php

namespace App\Filament\Resources\Payments;

use App\Enums\UserRole;
use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use App\Policies\PaymentPolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string $policy = PaymentPolicy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    public static function getNavigationGroup(): ?string
    {
        return __('resources.transactions');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        // Admin sees all payments
        if ($user->role === UserRole::ADMIN) {
            return $query;
        }

        // Owner sees payments for bookings on their vehicles
        if ($user->role === UserRole::OWNER) {
            return $query->whereHas('booking.vehicle', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        // Renter sees only their booking payments
        return $query->whereHas('booking', function ($q) use ($user) {
            $q->where('renter_id', $user->id);
        });
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        if ($user->role === UserRole::ADMIN) {
            return static::getModel()::where('payment_status', 'pending')->count();
        } elseif ($user->role === UserRole::OWNER) {
            return static::getModel()::whereHas('booking.vehicle', fn ($q) => $q->where('owner_id', $user->id))
                ->where('payment_status', 'pending')->count();
        } else {
            return static::getModel()::whereHas('booking', fn ($q) => $q->where('renter_id', $user->id))
                ->where('payment_status', 'pending')->count();
        }
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['transaction_id'];
    }

    public static function getGlobalSearchResultsLimit(): int
    {
        return 5;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('resources.transaction_id') => $record->transaction_id ?? '—',
            __('resources.amount') => 'RM '.number_format($record->amount, 2),
            __('resources.status') => $record->payment_status?->label() ?? '—',
            __('resources.method') => $record->payment_method?->label() ?? '—',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['booking.renter', 'booking.vehicle']);
    }
}

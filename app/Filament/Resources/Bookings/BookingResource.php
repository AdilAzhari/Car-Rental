<?php

namespace App\Filament\Resources\Bookings;

use App\Enums\UserRole;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use App\Policies\BookingPolicy;
use App\Services\FilamentQueryOptimizationService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string $policy = BookingPolicy::class;

    public static function getNavigationLabel(): string
    {
        return __('resources.bookings');
    }

    public static function getModelLabel(): string
    {
        return __('resources.booking');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.bookings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources.car_rental');
    }

    protected static ?int $navigationSort = 1;

    protected static string|null|BackedEnum $navigationIcon = Heroicon::ArchiveBoxArrowDown;

    public static function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
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
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $filamentQueryOptimizationService = app(FilamentQueryOptimizationService::class);

        $builder = $filamentQueryOptimizationService->getOptimizedBookingQuery()
            ->when(auth()->user()->role === UserRole::RENTER, fn ($q) => $q->where('renter_id', auth()->id()))
            ->when(auth()->user()->role === UserRole::OWNER, fn ($q) => $q->whereHas('vehicle', function ($vehicleQuery): void {
                $vehicleQuery->where('owner_id', auth()->id());
            }));

        // Apply performance monitoring in debug mode
        return $filamentQueryOptimizationService->monitorQueryPerformance($builder, 'BookingResource');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['id'];
    }

    public static function getGlobalSearchResultsLimit(): int
    {
        return 5;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('resources.booking') => 'BK-'.str_pad((string) $record->id, 6, '0', STR_PAD_LEFT),
            __('resources.renter') => $record->renter->name ?? '—',
            __('resources.vehicle') => $record->vehicle ? "{$record->vehicle->make} {$record->vehicle->model}" : '—',
            __('resources.status') => ucfirst($record->status->value ?? $record->status),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['renter', 'vehicle']);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

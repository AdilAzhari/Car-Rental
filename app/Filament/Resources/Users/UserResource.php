<?php

namespace App\Filament\Resources\Users;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Policies\UserPolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string $policy = UserPolicy::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return __('resources.user_management');
    }

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        // Only admins can see users in navigation
        $user = auth()->user();

        return $user && $user->role === UserRole::ADMIN;
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.users');
    }

    public static function getModelLabel(): string
    {
        return __('resources.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.users');
    }
    protected static ?string $recordTitleAttribute = 'User';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

            // RelationManagers\BookingsRelationManager::class,
            RelationManagers\VehiclesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 100 ? 'warning' : 'primary';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultsLimit(): int
    {
        return 5;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('resources.email') => $record->email,
            __('resources.phone') => $record->phone ?: '—',
            __('resources.role') => ucfirst($record->role->value ?? $record->role),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    // #[\Override]
    // public static function getEloquentQuery(): Builder
    // {
    //     $user = auth()->user();
    //     $filamentQueryOptimizationService = app(FilamentQueryOptimizationService::class);

    //     $query = $filamentQueryOptimizationService->getOptimizedUserQuery()
    //         ->when($user && $user->role !== UserRole::ADMIN, fn ($q) =>
    //             // Non-admin users can only see their own profile
    //             $q->where('id', $user->id))
    //         ->when(! $user, fn ($q) =>
    //             // If no authenticated user, return empty results
    //             $q->whereRaw('1 = 0'));

    //     // Apply performance monitoring
    //     return $filamentQueryOptimizationService->monitorQueryPerformance($query, 'UserResource');
    // }
}

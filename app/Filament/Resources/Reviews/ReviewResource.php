<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\CreateReview;
use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\Schemas\ReviewInfolist;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;


class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-star';

    public static function getNavigationGroup(): ?string
    {
        return __('resources.customer_feedback');
    }

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('resources.reviews');
    }

    public static function getModelLabel(): string
    {
        return __('resources.review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.reviews');
    }

    public static function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
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
            'index' => ListReviews::route('/'),
            'create' => CreateReview::route('/create'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $totalCount = static::getModel()::count();

        return $totalCount > 0 ? 'primary' : 'gray';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['comment'];
    }

    public static function getGlobalSearchResultsLimit(): int
    {
        return 5;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('resources.reviewer') => $record->renter->name ?? '—',
            __('resources.rating') => str_repeat('⭐', (int) $record->rating).' ('.$record->rating.'/5)',
            __('resources.vehicle') => $record->vehicle ? "{$record->vehicle->make} {$record->vehicle->model}" : '—',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['renter', 'vehicle']);
    }

    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->user()->role === 'renter', fn ($query) => $query->where('renter_id', auth()->id()))
            ->when(auth()->user()->role === 'owner', fn ($query) => $query->whereHas('vehicle', function ($vehicleQuery): void {
                $vehicleQuery->where('owner_id', auth()->id());
            }));
    }
}

<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Enums\VehicleStatus;
use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Database\Query\Builder;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'id';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label(__('resources.add_vehicle')),
        ];
    }

    public function getTabs(): array
    {
        $modelClass = $this->getResource()::getModel();

        // Calculate counts eagerly to avoid model context issues

        $allCount = $modelClass::count();
        $publishedCount = $modelClass::where('status', VehicleStatus::PUBLISHED)->count();
        $availableCount = $modelClass::where('status', VehicleStatus::PUBLISHED)->where('is_available', true)->count();
        $pendingCount = $modelClass::where('status', VehicleStatus::PENDING)->count();
        $approvedCount = $modelClass::where('status', VehicleStatus::APPROVED)->count();
        $rejectedCount = $modelClass::where('status', VehicleStatus::REJECTED)->count();
        $maintenanceCount = $modelClass::where('status', VehicleStatus::MAINTENANCE)->count();

        return [
            'all' => Tab::make(__('resources.all_vehicles'))
                ->badge($allCount),

            'published' => Tab::make(__('resources.published'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VehicleStatus::PUBLISHED))
                ->badge($publishedCount)
                ->icon('heroicon-m-check-circle'),

            'available' => Tab::make(__('resources.available'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VehicleStatus::PUBLISHED)->where('is_available', true))
                ->badge($availableCount)
                ->icon('heroicon-m-hand-thumb-up'),

            'pending' => Tab::make(__('resources.pending'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VehicleStatus::PENDING))
                ->badge($pendingCount)
                ->icon('heroicon-m-clock'),

            'approved' => Tab::make(__('resources.approved'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VehicleStatus::APPROVED))
                ->badge($approvedCount)
                ->icon('heroicon-m-check-badge'),

            'rejected' => Tab::make(__('resources.rejected'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VehicleStatus::REJECTED))
                ->badge($rejectedCount)
                ->icon('heroicon-m-x-circle'),

            'maintenance' => Tab::make(__('resources.maintenance'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VehicleStatus::MAINTENANCE))
                ->badge($maintenanceCount)
                ->icon('heroicon-m-wrench-screwdriver'),
        ];
    }
}

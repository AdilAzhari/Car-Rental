<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\User;
use App\Notifications\VehicleCreated;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Notification;

class CreateVehicle extends CreateRecord
{
    protected static string $resource = VehicleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the owner_id to the currently authenticated user
        $data['owner_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Notify all admins about the new vehicle submission
        $admins = User::where('role', UserRole::ADMIN)->get();

        Notification::send($admins, new VehicleCreated($this->record));
    }
}

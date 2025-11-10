<?php

namespace App\DTOs;

use Carbon\Carbon;

readonly class VehicleSearchDTO
{
    public function __construct(
        public ?Carbon $startDate,
        public ?Carbon $endDate,
        public ?string $location,
        public ?float $priceMin,
        public ?float $priceMax,
        public ?string $transmission,
        public ?string $fuelType,
        public ?string $category,
        public ?int $seats,
        public string $sortBy = 'created_at',
        public string $sortOrder = 'desc',
        public int $perPage = 12
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            startDate: $request->start_date ? Carbon::parse($request->start_date) : null,
            endDate: $request->end_date ? Carbon::parse($request->end_date) : null,
            location: $request->location,
            priceMin: $request->price_min ? (float) $request->price_min : null,
            priceMax: $request->price_max ? (float) $request->price_max : null,
            transmission: $request->transmission,
            fuelType: $request->fuel_type,
            category: $request->category,
            seats: $request->seats ? (int) $request->seats : null,
            sortBy: $request->get('sort_by', 'created_at'),
            sortOrder: $request->get('sort_order', 'desc'),
            perPage: min($request->get('per_page', 12), 50)
        );
    }

    public function hasDateFilter(): bool
    {
        return $this->startDate && $this->endDate;
    }

    public function hasPriceFilter(): bool
    {
        return $this->priceMin || $this->priceMax;
    }

    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate?->toDateString(),
            'end_date' => $this->endDate?->toDateString(),
            'location' => $this->location,
            'price_min' => $this->priceMin,
            'price_max' => $this->priceMax,
            'transmission' => $this->transmission,
            'fuel_type' => $this->fuelType,
            'category' => $this->category,
            'seats' => $this->seats,
            'sort_by' => $this->sortBy,
            'sort_order' => $this->sortOrder,
            'per_page' => $this->perPage,
        ];
    }
}

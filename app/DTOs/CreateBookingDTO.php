<?php

namespace App\DTOs;

use Carbon\Carbon;

readonly class CreateBookingDTO
{
    public function __construct(
        public int $carId,
        public int $renterId,
        public Carbon $startDate,
        public Carbon $endDate,
        public int $durationDays,
        public string $paymentMethod,
        public ?string $paymentMethodId,
        public ?string $pickupLocation,
        public ?string $dropoffLocation,
        public ?string $specialRequests,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            carId: $data['car_id'],
            renterId: $data['renter_id'],
            startDate: Carbon::parse($data['start_date']),
            endDate: Carbon::parse($data['end_date']),
            durationDays: $data['duration_days'],
            paymentMethod: $data['payment_method'],
            paymentMethodId: $data['payment_method_id'] ?? null,
            pickupLocation: $data['pickup_location'] ?? null,
            dropoffLocation: $data['dropoff_location'] ?? null,
            specialRequests: $data['special_requests'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'car_id' => $this->carId,
            'renter_id' => $this->renterId,
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),
            'duration_days' => $this->durationDays,
            'payment_method' => $this->paymentMethod,
            'payment_method_id' => $this->paymentMethodId,
            'pickup_location' => $this->pickupLocation,
            'dropoff_location' => $this->dropoffLocation,
            'special_requests' => $this->specialRequests,
        ];
    }

    public function isValidDateRange(): bool
    {
        return $this->startDate->isBefore($this->endDate)
            && $this->startDate->isAfter(now()->startOfDay());
    }

    public function requiresPaymentMethodId(): bool
    {
        return in_array($this->paymentMethod, ['visa', 'credit', 'stripe']);
    }
}

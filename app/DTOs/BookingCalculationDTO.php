<?php

namespace App\DTOs;

readonly class BookingCalculationDTO
{
    public function __construct(
        public float $dailyRate,
        public int $totalDays,
        public float $subtotal,
        public float $insuranceFee,
        public float $taxAmount,
        public float $totalAmount,
        public string $currency = 'MYR'
    ) {}

    public static function calculate(float $dailyRate, int $totalDays, float $insuranceRate = 0.10, float $taxRate = 0.08): self
    {
        $subtotal = $dailyRate * $totalDays;
        $insuranceFee = $subtotal * $insuranceRate;
        $taxableAmount = $subtotal + $insuranceFee;
        $taxAmount = $taxableAmount * $taxRate;
        $totalAmount = $taxableAmount + $taxAmount;

        return new self(
            dailyRate: $dailyRate,
            totalDays: $totalDays,
            subtotal: $subtotal,
            insuranceFee: $insuranceFee,
            taxAmount: $taxAmount,
            totalAmount: $totalAmount,
        );
    }

    public function toArray(): array
    {
        return [
            'daily_rate' => number_format($this->dailyRate, 2),
            'total_days' => $this->totalDays,
            'subtotal' => number_format($this->subtotal, 2),
            'insurance_fee' => number_format($this->insuranceFee, 2),
            'tax_amount' => number_format($this->taxAmount, 2),
            'total_amount' => number_format($this->totalAmount, 2),
            'currency' => $this->currency,
        ];
    }

    public function getFormattedTotal(): string
    {
        return $this->currency.' '.number_format($this->totalAmount, 2);
    }
}

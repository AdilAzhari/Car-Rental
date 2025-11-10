<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'car_rental_payments';

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'payment_status',
        'transaction_id',
        'gateway_response',
        'processed_at',
        'refunded_at',
        'refund_amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'processed_at' => 'datetime',
            'refunded_at' => 'datetime',
            'gateway_response' => 'json',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

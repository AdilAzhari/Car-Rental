<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    protected $table = 'car_rental_logs';

    protected $fillable = [
        'vehicle_id',
        'action',
        'description',
        'user_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'json',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

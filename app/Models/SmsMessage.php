<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'plate_number',
        'message_sid',
        'from_number',
        'to_number',
        'direction',
        'message_body',
        'message_type',
        'status',
        'parsed_data',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'parsed_data' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vehicle associated with this SMS.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Mark this SMS as processed.
     */
    public function markAsProcessed(): bool
    {
        return $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark this SMS as failed.
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => 'failed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Scope to get only JPJ responses.
     */
    public function scopeJpjResponses($query)
    {
        return $query->where('message_type', 'jpj_response');
    }

    /**
     * Scope to get messages for a specific vehicle.
     */
    public function scopeForVehicle($query, int $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    /**
     * Scope to get messages for a specific plate number.
     */
    public function scopeForPlateNumber($query, string $plateNumber)
    {
        return $query->where('plate_number', $plateNumber);
    }

    /**
     * Scope to get inbound messages.
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /**
     * Scope to get outbound messages.
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }
}

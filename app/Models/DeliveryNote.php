<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNote extends Model
{
    protected $fillable = [
        'client_id',
        'driver_id',
        'vehicle_id',
        'created_by',
        'delivery_date',
        'total_weight',
        'total_amount',
        'service_fee_per_ton',
        'service_fee',
        'bag_fee',
        'transport_fee',
        'cash_amount',
        'transfer_amount',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'total_weight' => 'float',
        'total_amount' => 'float',
        'service_fee_per_ton' => 'float',
        'service_fee' => 'float',
        'bag_fee' => 'float',
        'transport_fee' => 'float',
        'cash_amount' => 'float',
        'transfer_amount' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}

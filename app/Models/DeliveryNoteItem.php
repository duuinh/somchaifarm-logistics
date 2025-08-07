<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'item_id',
        'quantity_kg',
        'quantity_bags',
        'quantity_tons',
        'quantity_units',
        'unit_multiplier',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity_kg' => 'decimal:2',
        'quantity_bags' => 'decimal:2',
        'quantity_tons' => 'decimal:2',
        'quantity_units' => 'decimal:2',
        'unit_multiplier' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

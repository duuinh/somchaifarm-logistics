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
        'unit_multiplier',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity_kg' => 'float',
        'quantity_bags' => 'float',
        'unit_multiplier' => 'float',
        'unit_price' => 'float',
        'total_price' => 'float',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'item_id',
        'quantity',
        'unit_type',
        'price_per_unit',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'float',
        'price_per_unit' => 'float',
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

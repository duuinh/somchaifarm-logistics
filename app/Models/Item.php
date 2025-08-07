<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'name',
        'item_type',
        'unit_type',
        'regular_price_per_kg',
        'regular_price_per_bag',
        'regular_price_per_ton',
        'regular_price_per_unit',
        'credit_price_per_kg',
        'credit_price_per_bag',
        'credit_price_per_ton',
        'credit_price_per_unit',
        'kg_per_bag_conversion',
    ];

    protected $casts = [
        'item_type' => 'string',
        'unit_type' => 'string',
        'regular_price_per_kg' => 'decimal:2',
        'regular_price_per_bag' => 'decimal:2',
        'regular_price_per_ton' => 'decimal:2',
        'regular_price_per_unit' => 'decimal:2',
        'credit_price_per_kg' => 'decimal:2',
        'credit_price_per_bag' => 'decimal:2',
        'credit_price_per_ton' => 'decimal:2',
        'credit_price_per_unit' => 'decimal:2',
        'kg_per_bag_conversion' => 'decimal:2',
    ];

    public function deliveryNoteItems(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'name',
        'regular_price_per_kg',
        'regular_price_per_bag',
        'credit_price_per_kg',
        'credit_price_per_bag',
        'kg_per_bag_conversion',
    ];

    protected $casts = [
        'regular_price_per_kg' => 'decimal:2',
        'regular_price_per_bag' => 'decimal:2',
        'credit_price_per_kg' => 'decimal:2',
        'credit_price_per_bag' => 'decimal:2',
        'kg_per_bag_conversion' => 'decimal:2',
    ];

    public function deliveryNoteItems(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}

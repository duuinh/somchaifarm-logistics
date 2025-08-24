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
        'regular_price_per_kg' => 'float',
        'regular_price_per_bag' => 'float',
        'credit_price_per_kg' => 'float',
        'credit_price_per_bag' => 'float',
        'kg_per_bag_conversion' => 'float',
    ];

}

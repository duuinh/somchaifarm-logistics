<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'type',
        'name',
        'latitude',
        'longitude',
        'is_active'
    ];
    
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_active' => 'boolean'
        ];
    }
    
    public function scopeOffice($query)
    {
        return $query->where('type', 'office')->where('is_active', true);
    }
    
    public function scopePickup($query)
    {
        return $query->where('type', 'pickup')->where('is_active', true);
    }
    
    public function scopeDelivery($query)
    {
        return $query->where('type', 'delivery')->where('is_active', true);
    }
    
    public function scopeService($query)
    {
        return $query->where('type', 'service')->where('is_active', true);
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

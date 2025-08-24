<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'license_plate',
        'province',
        'vehicle_type',
        'load_capacity',
        'gps_device_id',
        'gps_provider',
        'is_active',
        'color',
    ];

    protected $casts = [
        'load_capacity' => 'decimal:2',
        'gps_device_id' => 'integer',
        'is_active' => 'boolean',
    ];

    
    /**
     * Scope to get only vehicles with GPS tracking
     */
    public function scopeWithGps($query)
    {
        return $query->whereNotNull('gps_device_id');
    }
    
    /**
     * Scope to get only active vehicles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope to get vehicles by provider
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('gps_provider', $provider);
    }
    
    /**
     * Get formatted name
     */
    public function getFormattedNameAttribute(): string
    {
        return ($this->vehicle_type ?? 'รถ') . ' ' . $this->license_plate;
    }
    
    /**
     * Get display name for tracking system
     */
    public function getTrackingNameAttribute(): string
    {
        $prefix = ($this->vehicle_type ?? 'รถ');
        $plate = $this->license_plate;
        $province = $this->province ? ' ' . $this->province : '';
        
        return "{$prefix} {$plate}{$province}";
    }
    
    /**
     * Get short version of license plate
     */
    public function getShortnameAttribute(): string
    {
        return $this->license_plate;
    }
}

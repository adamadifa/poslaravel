<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'base_unit_id');
    }

    /**
     * Get formatted display name (avoids duplicating if name and short_name match, e.g. "Dus" instead of "Dus (dus)").
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->short_name && strcasecmp(trim($this->short_name), trim($this->name)) !== 0) {
            return "{$this->name} ({$this->short_name})";
        }

        return $this->name;
    }
}

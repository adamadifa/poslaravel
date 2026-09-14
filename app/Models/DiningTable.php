<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiningTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'table_number',
        'area',
        'capacity',
        'status',
        'current_sale_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function currentSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'current_sale_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(TableReservation::class);
    }

    public function merges(): HasMany
    {
        return $this->hasMany(TableMerge::class, 'primary_table_id');
    }
}

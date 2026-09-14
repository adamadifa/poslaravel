<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableMerge extends Model
{
    use HasFactory;

    protected $fillable = [
        'primary_table_id',
        'merged_table_id',
        'sale_id',
        'merged_at',
        'unmerged_at',
    ];

    protected function casts(): array
    {
        return [
            'merged_at' => 'datetime',
            'unmerged_at' => 'datetime',
        ];
    }

    public function primaryTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'primary_table_id');
    }

    public function mergedTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'merged_table_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}

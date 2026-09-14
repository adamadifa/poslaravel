<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItemAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_item_id',
        'staff_user_id',
        'status',
        'started_at',
        'completed_at',
        'duration_actual_minutes',
        'commission_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'duration_actual_minutes' => 'integer',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }
}

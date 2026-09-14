<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'counter_date',
        'prefix',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'counter_date' => 'date',
            'last_number' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}

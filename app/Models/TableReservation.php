<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'dining_table_id',
        'warehouse_id',
        'customer_id',
        'guest_name',
        'guest_phone',
        'guest_count',
        'reservation_date',
        'reservation_time',
        'duration_minutes',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'guest_count' => 'integer',
            'duration_minutes' => 'integer',
            'reservation_date' => 'date',
        ];
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

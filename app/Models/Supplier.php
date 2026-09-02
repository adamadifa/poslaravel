<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'tax_id',
        'payment_term_days',
        'is_active',
    ];

    protected $casts = [
        'payment_term_days' => 'integer',
        'is_active' => 'boolean',
    ];
}

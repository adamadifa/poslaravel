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

    public function consignmentProducts()
    {
        return $this->hasMany(Product::class, 'consignment_supplier_id');
    }

    public function consignmentReceipts()
    {
        return $this->hasMany(ConsignmentReceipt::class);
    }

    public function consignmentSettlements()
    {
        return $this->hasMany(ConsignmentSettlement::class);
    }

    public function consignmentReturns()
    {
        return $this->hasMany(ConsignmentReturn::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseReceipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }
}

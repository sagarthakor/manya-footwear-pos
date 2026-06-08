<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'company', 'phone', 'email', 'address',
        'gst_number', 'opening_balance', 'total_payable', 'total_paid', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'total_payable'   => 'decimal:2',
        'total_paid'      => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function getOutstandingAttribute(): float
    {
        return (float) $this->total_payable - (float) $this->total_paid;
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function grns()
    {
        return $this->hasMany(Grn::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}

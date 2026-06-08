<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_id', 'user_id', 'order_date', 'expected_date',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'paid_amount',
        'status', 'notes',
    ];

    protected $casts = [
        'order_date'      => 'date',
        'expected_date'   => 'date',
        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function grns()
    {
        return $this->hasMany(Grn::class);
    }

    public static function generatePoNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $last = self::where('po_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        $number = $last ? (int) substr($last->po_number, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}

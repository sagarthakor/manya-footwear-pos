<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    protected $fillable = [
        'grn_number', 'purchase_order_id', 'supplier_id', 'user_id',
        'received_date', 'subtotal', 'tax_amount', 'total_amount',
        'paid_amount', 'payment_status', 'invoice_number', 'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'subtotal'      => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'paid_amount'   => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

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
        return $this->hasMany(GrnItem::class);
    }

    public static function generateGrnNumber(): string
    {
        $prefix = 'GRN-' . date('Ymd') . '-';
        $last = self::where('grn_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        $number = $last ? (int) substr($last->grn_number, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}

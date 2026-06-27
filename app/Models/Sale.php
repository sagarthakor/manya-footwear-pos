<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number', 'customer_id', 'user_id',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount',
        'paid_amount', 'change_amount', 'payment_method', 'status', 'notes',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function saleReturn()
    {
        return $this->hasOne(SaleReturn::class);
    }

    public static function generateInvoiceNumber(): string
    {
        // India financial year: April 1 – March 31
        $month = (int) date('n');
        $year  = (int) date('Y');
        $fyStart = $month >= 4 ? $year : $year - 1;
        $fyEnd   = $fyStart + 1;
        $prefix  = 'INV-' . substr($fyStart, 2) . substr($fyEnd, 2) . '-';

        $last = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        $number = $last ? (int) substr($last->invoice_number, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}

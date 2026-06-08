<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id', 'ordered_quantity',
        'received_quantity', 'unit_price', 'tax_percent', 'total_price',
    ];

    protected $casts = [
        'ordered_quantity'  => 'integer',
        'received_quantity' => 'integer',
        'unit_price'        => 'decimal:2',
        'tax_percent'       => 'decimal:2',
        'total_price'       => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

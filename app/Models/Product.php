<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'name', 'sku', 'item_code', 'barcode', 'brand', 'size', 'color',
        'purchase_price', 'selling_price', 'mrp', 'tax_percent', 'stock_quantity',
        'alert_quantity', 'description', 'image', 'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'mrp'            => 'decimal:2',
        'tax_percent'    => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function getGstAmountAttribute(): float
    {
        return round((float) $this->selling_price * (float) $this->tax_percent / 100, 2);
    }

    public function getPriceWithGstAttribute(): float
    {
        return round((float) $this->selling_price + $this->gst_amount, 2);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class)->withDefault();
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->alert_quantity;
    }
}
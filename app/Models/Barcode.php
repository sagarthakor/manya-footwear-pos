<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    protected $fillable = ['barcode', 'product_id', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault(['name' => '—']);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'unused'   => 'secondary',
            'assigned' => 'primary',
            'active'   => 'success',
            default    => 'light',
        };
    }
}
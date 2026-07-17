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

    public static function toBase64Image(string $code): string
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $png = $generator->getBarcode($code, $generator::TYPE_CODE_128, 3, 60);

        return 'data:image/png;base64,' . base64_encode($png);
    }
}
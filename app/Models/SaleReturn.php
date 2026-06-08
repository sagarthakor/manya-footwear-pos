<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = [
        'return_number', 'sale_id', 'user_id', 'return_date',
        'return_amount', 'return_type', 'refund_method', 'reason',
    ];

    protected $casts = [
        'return_date'   => 'date',
        'return_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RET-' . date('Ymd') . '-';
        $last = self::where('return_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        $number = $last ? (int) substr($last->return_number, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}

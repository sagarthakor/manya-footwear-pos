<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'mobile', 'email', 'address', 'total_purchase', 'visit_count'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}

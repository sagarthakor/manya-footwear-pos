<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'mobile', 'email', 'address', 'total_purchase', 'visit_count', 'loyalty_points'];

    protected $casts = [
        'loyalty_points' => 'integer',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function earnPoints(int $points, string $reference): void
    {
        $newBalance = (int) $this->loyalty_points + $points;

        $this->loyaltyPoints()->create([
            'type'          => 'earn',
            'points'        => $points,
            'balance_after' => $newBalance,
            'reference'     => $reference,
        ]);

        $this->increment('loyalty_points', $points);
    }

    public function redeemPoints(int $points, string $reference): bool
    {
        if ((int) $this->loyalty_points < $points) {
            return false;
        }

        $newBalance = (int) $this->loyalty_points - $points;

        $this->loyaltyPoints()->create([
            'type'          => 'redeem',
            'points'        => $points,
            'balance_after' => $newBalance,
            'reference'     => $reference,
        ]);

        $this->decrement('loyalty_points', $points);

        return true;
    }
}

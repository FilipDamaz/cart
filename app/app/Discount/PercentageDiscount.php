<?php
namespace App\Discount;

use App\Models\Cart;

class PercentageDiscount implements DiscountInterface
{
    private float $threshold;
    private float $discountRate;

    public function __construct(float $threshold = 100.0, float $discountRate = 0.10)
    {
        $this->threshold = $threshold;
        $this->discountRate = $discountRate;
    }

    public function calculateDiscount(Cart $cart): float
    {
        return $cart->total_price > $this->threshold ? $cart->total_price * $this->discountRate : 0.0;
    }
}

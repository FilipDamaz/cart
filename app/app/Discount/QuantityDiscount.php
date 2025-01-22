<?php
namespace App\Discount;



use App\Models\Cart;

class QuantityDiscount implements DiscountInterface
{
    public function calculateDiscount(Cart $cart): float
    {
        $discount = 0.0;


        foreach ($cart->cartItems as $item) {
            $discount += floor($item->quantity / 5) * $item->price;
        }
        return $discount;
    }
}

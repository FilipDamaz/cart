<?php
namespace App\Discount;



use App\Models\Cart;

interface DiscountInterface
{
    public function calculateDiscount(Cart $cart): float;
}

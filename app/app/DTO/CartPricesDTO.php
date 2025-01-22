<?php
namespace App\DTO;

use App\Models\Cart;

class CartPricesDTO
{
    public $total_price;
    public $final_total_price;

    // Constructor accepts a Cart object, or defaults to 0 if the Cart is empty
    public function __construct(Cart $cart = null)
    {
        // If the Cart is null, set all fields to 0
        if (is_null($cart)) {
            $this->total_price = 0;
            $this->final_total = 0;
        } else {
            // Otherwise, set fields from the Cart object
            $this->total_price = $cart->total_price;
            $this->final_total_price = $cart->final_total_price;
        }
    }
}

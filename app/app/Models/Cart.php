<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'total_price',
        'final_total_price',
        'currency_id',
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }
}

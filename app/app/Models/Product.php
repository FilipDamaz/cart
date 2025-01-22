<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Define which attributes are mass assignable
    protected $fillable = ['title', 'price', 'tax_rate'];

    // Optional: Define table name explicitly if different
    // protected $table = 'products';

    // Optional: Define timestamps if you don't want to use the default
    // public $timestamps = false;
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}

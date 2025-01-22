<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'currency_id', 'price'];

    // Define relationship to the product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Define relationship to the currency
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}

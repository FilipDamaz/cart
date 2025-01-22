<?php
namespace App\Http\Controllers;

use App\DTO\CartPricesDTO;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Currency;
use App\Discount\PercentageDiscount;
use App\Discount\QuantityDiscount;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{

    public function __construct()
    {
        $this->CartService = new CartService();
    }

    public function create(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|int|exists:currencies,id', // Definiowanie waluty koszyka
            'products' => 'nullable|array',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.quantity' => 'required_with:products|integer|min:1|max:10',
        ]);

        $cart = $this->CartService->createCart($request->currency_id, $request->products);

        return response()->json(['message' => 'Cart created', 'cart' => $cart]);
    }



    public function addProduct(Request $request, $cartId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cart = $this->CartService->addProduct($request->product_id, $request->quantity, $cartId);

        return response()->json(['message' => 'Products added to cart', 'cart' => $cart]);
    }

    public function removeProduct(Request $request, $cartId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = $this->CartService->removeProduct($request->product_id, $request->quantity, $cartId);

        return response()->json(['message' => 'Product removed from cart', 'cart' => $cart]);
    }
}

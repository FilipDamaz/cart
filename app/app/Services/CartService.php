<?php
namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Currency;
use App\Discount\PercentageDiscount;
use App\Discount\QuantityDiscount;

class CartService
{
    private array $discounts = [];
    private Cart $cart;

    public function __construct()
    {
        $this->discounts[] = new QuantityDiscount();
        $this->discounts[] = new PercentageDiscount();
        $this->cart = new Cart();
    }

    /**
     * Get the best discount available for the cart.
     */
    protected function getBestDiscount(Cart $cart): float
    {
        return max(array_map(fn($discount) => $discount->calculateDiscount($cart), $this->discounts));
    }

    /**
     * Create a new cart with the given currency and products.
     */
    public function createCart(int $currencyId, array $products = null): Cart
    {
        $this->cart->currency_id = $currencyId;
        $this->cart->total_price = 0;
        $this->cart->final_total_price = 0;
        $this->cart->save();

        if ($products) {
            $total_price = $this->processProductsInCart($products);
            $this->updateCartPrices($this->cart, $total_price);
        }

        $this->cart->load('cartItems'); // Load associated cart items
        return $this->cart;
    }

    /**
     * Process the products in the cart, calculating the total price.
     */
    protected function processProductsInCart(array $products): float
    {
        $total_price = 0;
        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['product_id']);
            $productPriceCartCurrency = $this->getProductPriceForCartCurrency($product);
            $this->addProductToCart($this->cart->id, $productData['product_id'], $productData['quantity'], $productPriceCartCurrency);
            $total_price += $productPriceCartCurrency['price'] * $productData['quantity'];
        }

        return $total_price;
    }

    /**
     * Add a product to the cart and update the cart's price.
     */
    public function addProduct(int $productId, int $quantity, int $cartId): Cart
    {
        $this->cart = Cart::findOrFail($cartId);
        $cartItem = CartItem::where('cart_id', $this->cart->id)->where('product_id', $productId)->first();

        $product = Product::findOrFail($productId);
        $productPriceCartCurrency = $this->getProductPriceForCartCurrency($product);

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > 10) {
                abort(422, 'Cannot add more than 10 items of this product to the cart.');
            }
            $cartItem->increment('quantity', $quantity);
        } else {
            $this->addProductToCart($this->cart->id, $productId, $quantity, $productPriceCartCurrency);
        }

        $newTotalPrice = $this->cart->total_price + $productPriceCartCurrency['price'] * $quantity;
        $this->updateCartPrices($this->cart, $newTotalPrice);
        $this->cart->load('cartItems'); // Load associated cart items

        return $this->cart;
    }

    /**
     * Remove a product from the cart and update the cart's price.
     */
    public function removeProduct(int $productId, int $quantity, int $cartId): Cart
    {
        $this->cart = Cart::findOrFail($cartId);
        $cartItem = CartItem::where('cart_id', $this->cart->id)->where('product_id', $productId)->first();

        if (!$cartItem) {
            abort(404, 'Product not found in the cart');
        }

        $this->processCartItemRemoval($cartItem, $quantity);

        $product = Product::findOrFail($productId);
        $productPriceCartCurrency = $this->getProductPriceForCartCurrency($product);

        $newTotalPrice = $this->cart->total_price - $productPriceCartCurrency['price'] * $quantity;
        $this->updateCartPrices($this->cart, $newTotalPrice);
        $this->cart->load('cartItems'); // Load associated cart items

        return $this->cart;
    }

    /**
     * Handle the removal of a cart item (adjust quantity or delete).
     */
    protected function processCartItemRemoval(CartItem $cartItem, int $quantity): void
    {
        if ($cartItem->quantity > $quantity) {
            $cartItem->decrement('quantity', $quantity);
        } else {
            $cartItem->delete();
        }
    }

    /**
     * Get the price for a product based on the cart's currency.
     */
    protected function getProductPriceForCartCurrency(Product $product): array
    {
        $prices = json_decode($product->price, true) ?? [];
        $cartCurrency = Currency::find($this->cart->currency_id);

        if (!$cartCurrency) {
            throw new \Exception('Cart currency not found');
        }

        $price = $prices[$cartCurrency->code] ?? null;

        if (!$price) {
            $defaultCurrency = config('currency.default_currency');
            if (!isset($prices[$defaultCurrency])) {
                throw new \Exception('No price for selected or default currency ' . $defaultCurrency);
            }
            $price = $prices[$defaultCurrency] * $cartCurrency->exchange_rate;
        }

        return [
            'price' => $price,
            'tax_rate' => $product->tax_rate,
        ];
    }

    /**
     * Add a product to the cart.
     */
    protected function addProductToCart(int $cartId, int $productId, int $quantity, array $priceCartCurrency): void
    {
        CartItem::create([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $priceCartCurrency['price'],
            'tax_rate' => $priceCartCurrency['tax_rate'],
        ]);
    }

    /**
     * Update the cart's price and apply any discounts.
     */
    protected function updateCartPrices(Cart $cart, float $totalPrice): void
    {
        $cart->update([
            'total_price' => $totalPrice,
            'final_total_price' => $totalPrice,
        ]);

        $bestDiscount = $this->getBestDiscount($cart);
        if ($bestDiscount > 0) {
            $cart->final_total_price = $cart->total_price - $bestDiscount;
            $cart->save();
        }
    }
}

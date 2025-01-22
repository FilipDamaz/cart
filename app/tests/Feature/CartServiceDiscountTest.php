<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceDiscountTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test data

        $this->productA = Product::create([
            'title' => 'Product A',
            'price' => json_encode(['USD' => 50, 'EUR' => 45]),
            'tax_rate' => 10,
        ]);

        $this->productB = Product::create([
            'title' => 'Product B',
            'price' => json_encode(['USD' => 30, 'EUR' => 27]),
            'tax_rate' => 5,
        ]);

        // Instantiate the CartService
        $this->cartService = new CartService();
    }

    public function test_create_cart_with_no_discounts()
    {
        $currency = Currency::where('code', 'USD')->first();

        // Create a cart with no discounts (single product, small quantity)
        $cart = $this->cartService->createCart($currency->id, [
            ['product_id' => $this->productA->id, 'quantity' => 1],
        ]);

        $this->assertEquals(50, $cart->total_price);
        $this->assertEquals(50, $cart->final_total_price); // No discounts applied
    }

    public function test_create_cart_with_quantity_discount()
    {
        $currency = Currency::where('code', 'USD')->first();

        $cart = $this->cartService->createCart($currency->id, [
            ['product_id' => $this->productA->id, 'quantity' => 10], // Quantity triggers discount 10/5 =2 2*50 = 100 discout , final total = 500 -100 = 400
        ]);

        $this->assertEquals(500, $cart->total_price);
        $this->assertEquals(400, $cart->final_total_price); // Discount applied
    }

    public function test_create_cart_with_percentage_discount()
    {
        $currency = Currency::where('code', 'USD')->first();

        // Mock PercentageDiscount to return a fixed discount amount

        $cart = $this->cartService->createCart($currency->id, [
            ['product_id' => $this->productB->id, 'quantity' => 4], // Quantity triggers percentage discount
        ]);

        $this->assertEquals(120, $cart->total_price);
        $this->assertEquals(108, $cart->final_total_price); // Discount applied 10% of 120 = 12
    }

    public function test_best_discount_is_applied()
    {
        $currency = Currency::where('code', 'USD')->first();

        $cart = $this->cartService->createCart($currency->id, [
            ['product_id' => $this->productA->id, 'quantity' => 5],
        ]);

        $this->assertEquals(250.0, $cart->total_price);
        $this->assertEquals(200.0, $cart->final_total_price); // Best discount applied (QuantityDiscount) -> one free item
    }

    public function test_discount_recalculation_after_removal()
    {
        $currency = Currency::where('code', 'USD')->first();

        $cart = $this->cartService->createCart($currency->id, [
            ['product_id' => $this->productA->id, 'quantity' => 5],
        ]);
        $this->assertEquals(250, $cart->total_price);
        $this->assertEquals(200, $cart->final_total_price); //quantity discount applied

        // Remove products
        $updatedCart = $this->cartService->removeProduct($this->productA->id, 2, $cart->id);

        $this->assertEquals(150.0, $updatedCart->total_price);
        $this->assertEquals(135.0, $updatedCart->final_total_price); // Discount recalculated now 3 items left so procetage discount applied

        $updatedCart = $this->cartService->removeProduct($this->productA->id, 2, $cart->id);

        $this->assertEquals(50.0, $updatedCart->total_price);
        $this->assertEquals(50.0, $updatedCart->final_total_price); // no discount
    }
}

<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Currency;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    // Create test data and initial setup
    protected function setUp(): void
    {
        parent::setUp();

        // Seed or create required models for the test (products, currencies)
        Currency::factory()->create(['name' => 'USD dolar', 'code' => 'AAA', 'id'=>10, 'exchange_rate' => 1.0]);
        Currency::factory()->create(['name' => 'Euro', 'code' => 'BBB', 'id'=>20, 'exchange_rate' => 0.9]);

        $product = Product::create([
            'title' => 'Fallout2',
            'price' => json_encode(['AAA' => 1.99, 'BBB' => 2.49]),
            'tax_rate' => 10,
        ]);
    }

    // Test creating a cart
    public function test_create_cart()
    {
        $currency = Currency::first();

        $response = $this->postJson('/api/cart/create', [
            'currency_id' => $currency->id,
            'products' => [
                ['product_id' => 1, 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Cart created',
        ]);
        $this->assertDatabaseHas('carts', ['currency_id' => $currency->id]);
    }

    // Test adding a product to the cart
    public function test_add_product_to_cart()
    {
        $currency = Currency::first();
        $cart = Cart::create([
            'currency_id' => $currency->id,
            'total_price' => 0,  // Provide a value
            'final_total_price' => 0, // Provide a value
        ]);

        $response = $this->postJson('/api/cart/add/'. $cart->id, [
            'product_id' => 1,
            'quantity' => 2
        ]);

        $response->assertStatus(200);
        $message = json_decode($response->getContent(), true)['message'];
        $this->assertEquals('Products added to cart', $message);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => 1,
            'quantity' => 2
        ]);
    }

    // Test removing a product from the cart
    public function test_remove_product_from_cart()
    {
        $currency = Currency::first();
        $cart = Cart::create([
            'currency_id' => $currency->id,
            'total_price' => 2.19,  // Provide a value
            'final_total_price' => 2.19, // Provide a value
        ]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => 1,
            'quantity' => 3,
            'price' => 2.19,
            'tax_rate' => 10
        ]);

        $response = $this->deleteJson('/api/cart/remove/'. $cart->id, [
            'product_id' => 1,
            'quantity' => 2
        ]);

        $response->assertStatus(200);

        $message = json_decode($response->getContent(), true)['message'];
        $this->assertEquals('Product removed from cart', $message);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => 1,
            'quantity' => 1
        ]);
    }

    // Test error when trying to remove a product that does not exist in the cart
    public function test_remove_product_not_found_in_cart()
    {
        $currency = Currency::first();
        $cart = Cart::create([
            'currency_id' => $currency->id,
            'total_price' => 0, // Provide a value
            'final_total_price' => 0, // Provide a value
        ]);

        $response = $this->deleteJson('/api/cart/remove/'. $cart->id, [
            'product_id' => 1,
            'quantity' => 1
        ]);
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Product not found in the cart'
        ]);
    }

    // Test validation when adding product to cart without a valid product
    public function test_add_product_validation_fail()
    {
        $currency = Currency::first();
        $cart = $cart = Cart::create([
            'currency_id' => $currency->id,
            'total_price' => 0, // Provide a value
            'final_total_price' => 0, // Provide a value
        ]);

        $response = $this->postJson('/api/cart/add/'. $cart->id, [
            'product_id' => 999, // Invalid product ID
            'quantity' => 2
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('product_id');
    }

    // Test validation when removing product from cart without specifying quantity
    public function test_remove_product_validation_fail()
    {

        $currency = Currency::first();
        $cart = Cart::create([
            'currency_id' => $currency->id,
            'total_price' => 0, // Provide a value
            'final_total_price' => 0, // Provide a value
        ]);
        $response = $this->deleteJson('/api/cart/remove/'. $cart->id, [
            'product_id' => 1, // Product ID does not exist in the cart
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('quantity');
    }
}

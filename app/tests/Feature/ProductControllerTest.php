<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Factories\ProductFactory;
use Faker\Core\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test for creating a product (store method)
    public function test_store_product()
    {
        // Define the product data
        $data = [
            'title' => 'Test Product'.rand(60, 100000),
            'tax_rate' => 10,
            'price' => [
                'USD' => 100,
                'EUR' => 86,
            ],
        ];
        // Make a POST request to the product store endpoint
        $response = $this->postJson(route('products.store'), $data);

        // Assert the response status code is 201 (Created)
        $response->assertStatus(201);

        // Retrieve the product from the database
        $product = \App\Models\Product::where('title', $data['title'])->first();

        // Assert the product data in the database
        $this->assertNotNull($product);
        $this->assertEquals($data['title'], $product->title);
        $this->assertEquals(number_format($data['tax_rate'], 2), $product->tax_rate);
        $storedPrice = json_decode($product->price, true);
        $this->assertEquals($data['price'], $storedPrice);
    }

    // Test for fetching products (index method)
    public function test_index_products()
    {
        Product::factory()->create(['title' => 'Test Product0', 'tax_rate' => 10, 'price' => json_encode(['USD' => 100, 'EUR' => 90])]);
        Product::factory()->create(['title' => 'Test Product1', 'tax_rate' => 10, 'price' => json_encode(['USD' => 100, 'EUR' => 90])]);
        Product::factory()->create(['title' => 'Test Product2', 'tax_rate' => 10, 'price' => json_encode(['USD' => 100, 'EUR' => 90])]);
        Product::factory()->create(['title' => 'Test Product3', 'tax_rate' => 10, 'price' => json_encode(['USD' => 100, 'EUR' => 90])]);
        Product::factory()->create(['title' => 'Test Product4', 'tax_rate' => 10, 'price' => json_encode(['USD' => 100, 'EUR' => 90])]);

        $response = $this->getJson(route('products.index'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'price','tax_rate', 'created_at', 'updated_at'],
            ],
        ]);
    }

    // Test for updating a product (update method)
    public function test_update_product()
    {
        $product = Product::create([
            'title' => 'Old Product',
            'tax_rate' => 10,
            'price' => json_encode(['USD' => 100, 'EUR' => 90]),
        ]);

        $data = [
            'title' => 'Old Product2',
            'tax_rate' => 10,
            'price' => ['USD' => 120, 'GBP' => 80],
        ];

        $response = $this->putJson(route('products.update', $product->id), $data);

        $response->assertStatus(Response::HTTP_OK);
        $storedPrice = json_decode(json_decode($response->getContent())->price, true);
        $this->assertEquals(['USD' => 120, 'EUR' => 90, 'GBP' => 80], $storedPrice);
    }
//
//    // Test for deleting a product (destroy method)
    public function test_destroy_product()
    {

        $product = Product::create([
            'title' => 'Product to delete',
            'tax_rate' => 10,
            'price' => json_encode(['USD' => 100, 'EUR' => 90]),
        ]);

        $response = $this->deleteJson(route('products.destroy', $product->id));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment(['message' => 'Product deleted']);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

//    // Test for validation of product creation (missing price for default currency)
    public function test_store_product_missing_default_currency_price()
    {

        $data = [
            'title' => 'Invalid Product',
            'tax_rate' => 10,
            'price' => ['EUR' => 120, 'GBP' => 80],
        ];

        $response = $this->postJson(route('products.store'), $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['message' => 'Price for USD is required.']);
    }

//    // Test for validation of missing title
    public function test_store_product_missing_title()
    {
        $data = [
            'tax_rate' => 10,
            'price' => ['EUR' => 120, 'GBP' => 80],
        ];

        $response = $this->postJson(route('products.store'), $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['title' => ['The title field is required.']]);
    }
}


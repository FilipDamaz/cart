<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Default currency code

    // Create a new product with prices for different currencies
    public function store(Request $request)
    {
        $this->validateProductRequest($request);

        $productData = $this->parseProductRequest($request);
        $product = Product::create($productData);
        return response()->json($product, 201);
    }


    public function index(Request $request)
    {
        return Product::paginate(3);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $this->validateProductRequest($request, true);

        $existingPrices = json_decode($product->price, true) ?? [];
        foreach ($request->price as $currency=>$price) {
            $existingPrices[$currency] = $price;
        }

        $product->update([
            'tax_rate' => $request->tax_rate,
            'title' => $request->title,
            'price' => json_encode($existingPrices),
        ]);

        return response()->json($product);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    /**
     * Validate the product request.
     *
     * @param Request $request
     * @param bool $isUpdate
     */
    private function validateProductRequest(Request $request, bool $isUpdate = false)
    {
        if($isUpdate) {
            $rules = [
                'price' => 'required|array',
                'title' => 'required'
            ];
        } else {
            $rules = [
                'price' => 'required|array',
                'title' => 'required|unique:products'
            ];
        }
        $defaultCurrency = config('currency.default_currency');

        $request->validate($rules);

        if (!array_key_exists($defaultCurrency, $request->price)) {
            abort(422, 'Price for ' . $defaultCurrency . ' is required.');
        }

        if($isUpdate)
        {
            $existingProduct = Product::where('title', $request->title)->first();
            if($existingProduct && $existingProduct->id != $request->id)
            {
                abort(422, 'Product with this title already exists.');
            }
        }
    }

    /**
     * Parse the product request to extract product data.
     *
     * @param Request $request
     * @return array
     */
    private function parseProductRequest(Request $request): array
    {
        $prices = [];
        foreach ($request->price as $currency=>$price) {
            $prices[$currency] = $price;
        }

        return [
            'tax_rate' => $request->tax_rate,
            'title' => $request->title,
            'price' => json_encode($prices),
        ];
    }
}

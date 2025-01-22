<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::apiResource('products', ProductController::class);
Route::post('cart/create', [CartController::class, 'create']);
Route::post('cart/add/{id}', [CartController::class, 'addProduct']);
Route::delete('cart/remove/{id}', [CartController::class, 'removeProduct']);
Route::get('cart', [CartController::class, 'list']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

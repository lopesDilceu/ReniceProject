<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

/*
| Public REST API, version 1 (/api/v1). Bearer tokens from Sanctum; each
| token carries scopes: "read" for GET, "write" for anything that changes
| the cart or places orders. The OpenAPI document is generated from these
| routes (docs/api/openapi.json, browsable at /docs/api).
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/tokens', [TokenController::class, 'store'])->middleware('throttle:api-tokens')->name('tokens.store');

    Route::middleware('throttle:api')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::delete('/tokens/current', [TokenController::class, 'destroy'])->name('tokens.destroy');

        Route::middleware('abilities:read')->group(function () {
            Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
            Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        });

        Route::middleware('abilities:write')->group(function () {
            Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
            Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
            Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
            // Same rule as the web checkout: a confirmed e-mail.
            Route::post('/orders', [OrderController::class, 'store'])->middleware('verified')->name('orders.store');
        });
    });
});

<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\AvatarController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\PhoneController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SupplierPurchaseController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ReviewController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'home')->name('home');
Route::inertia('/sobre', 'about')->name('sobre');

// v1 catalog URLs. Reviews now live on each product page. Registered before
// /produtos/{product} so "pesquisa" is not taken for a product slug.
Route::get('/produtos/pesquisa', fn (Request $request) => redirect()->route('products.index', array_filter(['q' => $request->query('query')]), 301));
Route::permanentRedirect('/avaliacoes', '/produtos');

Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Authentication routes (/login, /register, /logout, /forgot-password, ...) are
// registered by Laravel Fortify; see FortifyServiceProvider.

// Cart, checkout and orders. The cart is always the signed-in customer's own:
// no route takes a cart id (SEC-02, SEC-03). Checkout is a CSRF-protected POST
// (SEC-04) and requires a verified e-mail.
Route::middleware('auth')->group(function () {
    Route::get('/carrinho', [CartController::class, 'show'])->name('cart.show');
    Route::post('/carrinho/itens', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/carrinho/itens/{item}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/carrinho/itens/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');

    Route::post('/enderecos', [AddressController::class, 'store'])->name('addresses.store');

    Route::middleware('verified')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    });

    Route::get('/conta/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');

    // The account always acts on the signed-in user: no route takes a user id
    // (v1's PUT /usuarios/edit/{id} let anyone edit anyone: SEC-01).
    Route::get('/conta', [ProfileController::class, 'edit'])->name('account.edit');
    Route::patch('/conta', [ProfileController::class, 'update'])->name('account.update');
    Route::post('/conta/foto', [AvatarController::class, 'store'])->name('account.avatar.store');
    Route::delete('/conta/foto', [AvatarController::class, 'destroy'])->name('account.avatar.destroy');
    Route::put('/conta/senha', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('account.password.update');
    Route::post('/conta/telefones', [PhoneController::class, 'store'])->name('phones.store');
    Route::delete('/conta/telefones/{phone}', [PhoneController::class, 'destroy'])->name('phones.destroy');
    Route::delete('/enderecos/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::post('/produtos/{product:slug}/avaliacoes', [ReviewController::class, 'store'])->name('reviews.store');
});

// Admin. Every route requires the 'admin' ability (see AppServiceProvider).
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('home');

    Route::get('/pedidos', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/pedidos/{order}/status', [AdminOrderController::class, 'update'])->name('orders.update');

    Route::get('/produtos', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/produtos/novo', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/produtos', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/produtos/{product}/editar', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/produtos/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/produtos/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/produtos/{product}/restaurar', [AdminProductController::class, 'restore'])
        ->withTrashed()
        ->name('products.restore');

    Route::get('/produtos/{product}/estoque', [InventoryController::class, 'show'])->withTrashed()->name('inventory.show');
    Route::post('/produtos/{product}/estoque', [InventoryController::class, 'store'])->name('inventory.store');

    Route::get('/compras', [SupplierPurchaseController::class, 'index'])->name('purchases.index');
    Route::post('/compras', [SupplierPurchaseController::class, 'store'])->name('purchases.store');

    Route::get('/clientes', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::patch('/clientes/{user}/admin', [AdminCustomerController::class, 'toggleAdmin'])->name('customers.toggle-admin');

    Route::get('/avaliacoes', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::delete('/avaliacoes/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
});

// v1 URLs, so old links and bookmarks keep working.
Route::permanentRedirect('/usuarios/login', '/login');
Route::permanentRedirect('/usuarios/cadastro', '/register');
Route::permanentRedirect('/usuarios/carrinho', '/carrinho');
Route::permanentRedirect('/usuarios/pagamento', '/checkout');
Route::permanentRedirect('/usuarios/minha-conta', '/conta');
Route::permanentRedirect('/usuarios/editar-dados', '/conta');
Route::permanentRedirect('/usuarios/minhas-compras', '/conta/pedidos');
Route::permanentRedirect('/adm', '/admin');
Route::get('/adm/{any}', fn () => redirect('/admin', 301))->where('any', '.*');

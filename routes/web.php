<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\AvatarController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\PhoneController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\ReviewController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Inertia + React pages (v2). Everything below the legacy marker is v1 Blade,
// replaced module by module in Phase 3 (ADR 0004).
Route::inertia('/', 'home')->name('home');
Route::inertia('/sobre', 'about')->name('sobre');

// v1 catalog URLs. Reviews now live on each product page. Registered before
// /produtos/{product} so "pesquisa" is not taken for a product slug.
Route::get('/produtos/pesquisa', fn (Request $request) => redirect()->route('products.index', array_filter(['q' => $request->query('query')]), 301));
Route::permanentRedirect('/avaliacoes', '/produtos');

Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Authentication routes (/login, /register, /logout, /forgot-password, ...) are
// registered by Laravel Fortify; see FortifyServiceProvider. The v1 URLs
// redirect permanently so old links and bookmarks keep working.
Route::permanentRedirect('/usuarios/login', '/login');
Route::permanentRedirect('/usuarios/cadastro', '/register');

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

// v1 cart, payment and account URLs.
Route::permanentRedirect('/usuarios/carrinho', '/carrinho');
Route::permanentRedirect('/usuarios/pagamento', '/checkout');
Route::permanentRedirect('/usuarios/minha-conta', '/conta');
Route::permanentRedirect('/usuarios/editar-dados', '/conta');
Route::permanentRedirect('/usuarios/minhas-compras', '/conta/pedidos');

// --- Legacy (v1) routes ---

// Rotas relacionadas à área administrativa
Route::prefix('adm')->middleware('admin')->group(function () {
    Route::get('/', function () {
        return view('adm.home');
    })->name('adm.home');

    Route::get('/estoque', function () {
        return view('adm.estoque.list');
    })->name('adm.estoque.list');

    Route::get('/produtos', function () {
        return view('adm.produtos.list');
    })->name('adm.produtos.list');

    Route::get('/avaliacoes', function () {
        return view('adm.avaliacoes.list');
    })->name('adm.avaliacoes.list');

    Route::get('/usuarios', function () {
        return view('adm.usuarios.list');
    })->name('adm.usuarios.list');

    Route::get('/vendas', function () {
        return view('adm.vendas.list');
    })->name('adm.vendas.list');

    Route::get('/compras', [CompraController::class, 'index'])->name('adm.compras.list');
    Route::post('/compras', [CompraController::class, 'store'])->name('adm.compras.store');
    Route::delete('/compras/{co_id}', [CompraController::class, 'destroy'])->name('adm.compras.destroy');

    Route::get('/estoque', [EstoqueController::class, 'index'])->name('adm.estoque.list');

    Route::get('/produtos', [ProdutoController::class, 'index'])->name('adm.produtos.list');
    Route::post('/produto', [ProdutoController::class, 'store'])->name('adm.produto.store');
    Route::put('/produtos/{pr_id?}', [ProdutoController::class, 'edit'])->name('adm.produto.edit');
    Route::delete('/produtos/{pr_id}', [ProdutoController::class, 'destroy'])->name('adm.produtos.destroy');

    Route::get('/usuarios', [UserController::class, 'index'])->name('adm.usuarios.list');
    Route::put('/usuarios/{pr_id?}', [UserController::class, 'edit'])->name('adm.usuario.edit');
    // Route::post('/produto', [ProdutoController::class, 'store'])->name('adm.produto.store');
    Route::delete('/usuarios/{pr_id}', [UserController::class, 'destroy'])->name('adm.usuarios.destroy');

    Route::get('/vendas', [VendaController::class, 'index'])->name('adm.vendas.list');

    Route::get('/avaliacoes', [AvaliacaoController::class, 'listAvaliacoes'])->name('adm.avaliacoes.list');
    Route::delete('/avaliacoes/{av_id}', [AvaliacaoController::class, 'destroy'])->name('adm.avaliacoes.destroy');
});

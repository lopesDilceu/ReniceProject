<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\UserController;
use App\Models\Estoque;
use Illuminate\Support\Facades\Route;

// Rotas principais do site
Route::get('/', function () {
    return view('main.home');
})->name('home');

Route::get('/sobre', function () {
    return view('main.sobre');
})->name('sobre');

Route::get('/avaliacoes', function () {
    return view('main.avaliacoes');
})->name('avaliacoes');

// Rotas relacionadas aos usuários
Route::prefix('usuarios')->group(function () {
    Route::get('/login', function () {
        return view('main.usuarios.login');
    })->name('usuarios.login');

    Route::post('/login', [UserController::class, 'login'])->name('usuarios.login.submit');

    Route::get('/cadastro', function () {
        return view('main.usuarios.create');
    })->name('usuarios.cadastro');

    Route::post('/logout', [UserController::class, 'logout'])->name('usuarios.logout.submit');

    Route::get('/editar', function () {
        return view('main.usuarios.edit');
    })->name('usuarios.edit');

    Route::get('/show', function () {
        return view('main.usuarios.show');
    })->name('usuarios.show');

    Route::get('/carrinho', function () {
        return view('main.usuarios.carrinho');
    })->name('usuarios.carrinho');

    Route::get('/pagamento', function () {
        return view('main.usuarios.pagamento');
    })->name('usuarios.pagamento');

    Route::get('/minhas-compras', function () {
        return view('main.usuarios.minhas-compras');
    })->name('usuarios.minhas-compras');
});

Route::resource('usuarios', UserController::class);

// Rotas relacionadas à área administrativa
Route::prefix('adm')->group(function () {
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
    // Route::post('/produto', [ProdutoController::class, 'store'])->name('adm.produto.store');
    Route::delete('/usuarios/{pr_id}', [UserController::class, 'destroy'])->name('adm.usuarios.destroy');
});


// Rota para listar produtos
Route::get('/produtos', [ProdutoController::class, 'indexHome'])->name('produtos');

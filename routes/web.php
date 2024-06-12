<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('main.home');
})->name('home');

Route::get('/sobre', function () {
    return view('main.sobre');
})->name('sobre');

// Rotas relacionadas aos usuários
Route::prefix('usuarios')->group(function () {
    Route::get('/login', function () {
        return view('main.usuarios.login');
    })->name('usuarios.login');

    Route::get('/cadastro', function () {
        return view('main.usuarios.create');
    })->name('usuarios.cadastro');

    Route::get('/pagamento', function () {
        return view('main.usuarios.venda.create');
    })->name('usuarios.pagamento');
});

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

    Route::get('/compras', function () {
        return view('adm.compras.list');
    })->name('adm.compras.list');
});

// Rotas relacionadas aos produtos
Route::get('/produtos', function () {
    return view('adm.produtos.home');
})->name('produtos');

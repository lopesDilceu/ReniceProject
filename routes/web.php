<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('main.home');
})->name('home');

Route::get('/sobre', function () {
    return view('main.sobre');
})->name('sobre');

Route::get('/produtos', function () {
    return view('adm.produtos.home');
})->name('produtos');

Route::get('/login', function () {
    return view('main.usuarios.login');
})->name('usuarios.login');

Route::get('/cadastro', function () {
    return view('main.usuarios.create');
})->name('usuarios.cadastro');

Route::get('/pagamento', function () {
    return view('main.usuarios.venda.create');
})->name('usuarios.pagamento');

Route::get('/adm', function () {
    return view('adm.home');
})->name('adm.home');

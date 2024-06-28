<?php

use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\ItensCarrinhoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendaController;
use App\Models\Estoque;
use Illuminate\Support\Facades\Route;

// Rotas principais do site
Route::get('/', function () {
    return view('main.home');
})->name('home');

Route::get('/sobre', function () {
    return view('main.sobre');
})->name('sobre');



Route::get('/avaliacoes', [AvaliacaoController::class, 'index'])->name('avaliacoes');

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

    Route::post('/store', [UserController::class, 'store'])->name('usuarios.store');



    Route::get('/carrinho', function () {
        return view('main.usuarios.carrinho');
    })->name('usuarios.carrinho');

    Route::get('/pagamento', function () {
        return view('main.usuarios.pagamento');
    })->name('usuarios.pagamento');

    Route::get('/minhas-compras', function () {
        return view('main.usuarios.minhas-compras');
    })->name('usuarios.minhas-compras');

    Route::get('/carrinho', [ItensCarrinhoController::class, 'index'])->name('usuario.carrinho');
    Route::delete('/carrinho/{ic_id}', [ItensCarrinhoController::class, 'destroy'])->name('usuario.carrinho.destroy');
    Route::get('/pagamento', [ItensCarrinhoController::class, 'indexPagamento'])->name('usuario.pagamento');
    Route::get('/finalizar/{ic_id_carrinho}', [ItensCarrinhoController::class, 'finalizarCompra'])->name('usuario.finalizar');
    Route::get('/compra-finalizada', [ItensCarrinhoController::class, 'compraFinalizada'])->name('compra-finalizada');


    Route::middleware('auth')->get('/minha-conta', [UserController::class, 'minhaConta'])->name('minha-conta');
    Route::middleware('auth')->get('/editar-dados', [UserController::class, 'editar'])->name('editar');
    Route::put('/edit/{us_id}', [UserController::class, 'edit'])->name('usuarios.edit');

    Route::get('/minhas-compras', [VendaController::class, 'minhasCompras'])->name('minhas-compras');
    

});



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


// Rota para listar produtos
Route::get('/produtos', [ProdutoController::class, 'indexHome'])->name('produtos');
Route::get('/avaliar-produto/{produto_id}', [ProdutoController::class, 'avaliarProduto'])->name('avaliar.produto');
Route::post('/salvar-avaliacao', [ProdutoController::class, 'salvarAvaliacao'])->name('salvar.avaliacao');
Route::get('/produtos/pesquisa', [ProdutoController::class, 'pesquisa'])->name('pesquisa');

//Rota para armazenar no carrinho
Route::post('/carrinho/store', [ItensCarrinhoController::class, 'store'])->name('carrinho.store');
Route::patch('/carrinho/{id}', [ItensCarrinhoController::class, 'update'])->name('usuario.carrinho.update');




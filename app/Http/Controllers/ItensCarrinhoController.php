<?php

namespace App\Http\Controllers;

use App\Models\Carrinho;
use App\Models\ItensCarrinho;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItensCarrinhoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $carrinho = Carrinho::where('ca_id_usuario', Auth::id())->first();
        $itenscarrinho = ItensCarrinho::where('ic_id_carrinho', $carrinho->ca_id)->get(); 
        // dd($itenscarrinho);
        $produtos = Produto::all();
        return view('main.usuarios.carrinho', compact('itenscarrinho', 'produtos', 'carrinho'));

    }

    public function indexPagamento()
    {
        //
        $carrinho = Carrinho::where('ca_id_usuario', Auth::id())->first();
        $itenscarrinho = ItensCarrinho::where('ic_id_carrinho', $carrinho->ca_id)->get(); 
        $produtos = Produto::all();
        return view('main.usuarios.pagamento', compact('itenscarrinho', 'produtos', 'carrinho'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $produto_id = $request->input('pr_id');
        $quantidade = $request->input('ic_quantidade', 1); // Valor padrão se não especificado
    
        // Verificar se o usuário está autenticado
        if (Auth::check()) {
            // Buscar o carrinho do usuário logado
            $carrinho = Carrinho::where('ca_id_usuario', Auth::id())->first();
    
            // Se o carrinho não existir, criar um novo
            if (!$carrinho) {
                $carrinho = new Carrinho();
                $carrinho->ca_id_usuario = Auth::id();
                $carrinho->save();
            }

    
            // Criar um novo item de carrinho
            $item = new ItensCarrinho();
            $item->ic_id_carrinho = $carrinho->ca_id;
            $item->ic_id_produto = $produto_id;
            $item->ic_quantidade = $quantidade;
            $item->save();
    
            return redirect()->back()->with('success', 'Produto adicionado ao carrinho com sucesso.');
        } else {
            return redirect()->route('login')->with('error', 'Faça login para adicionar produtos ao carrinho.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $itemcarrinho = ItensCarrinho::findOrFail($id);

                // Exclua a compra
        $itemcarrinho->delete();

                toastr()->success('Produto excluído com sucesso');
        
                // Redirecione de volta à página de compras ou faça qualquer outra coisa que você queira
                return back();
    }

    public function finalizarCompra($id)
    {
        // Chama o procedimento armazenado no banco de dados MySQL
        DB::statement("CALL finalizar_venda($id)");

        // Redireciona o usuário para uma página de confirmação ou outra rota
        toastr()->success('Compra finalizada com sucesso');
        return redirect()->route('home');
    }

    public function confirmacaoPagamento()
    {
        // Aqui você pode retornar uma view de confirmação de pagamento
        return view('usuario.confirmacao_pagamento');
    }
}

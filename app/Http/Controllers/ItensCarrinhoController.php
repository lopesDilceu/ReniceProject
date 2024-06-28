<?php

namespace App\Http\Controllers;

use App\Models\Carrinho;
use App\Models\Endereco;
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
        $carrinho = Carrinho::firstOrCreate(
            ['ca_id_usuario' => Auth::id()],
            ['ca_id_usuario' => Auth::id()] // Os valores adicionais que devem ser definidos ao criar um novo carrinho
        );
        $itenscarrinho = ItensCarrinho::where('ic_id_carrinho', $carrinho->ca_id)->get(); 
        // dd($itenscarrinho);
        $produtos = Produto::all();
        return view('main.usuarios.carrinho', compact('itenscarrinho', 'produtos', 'carrinho'));

    }

    public function indexPagamento()
    {
        //
        $carrinho = Carrinho::where('ca_id_usuario', Auth::id())->first();
        $endereco = Endereco::where('en_usuario_id', Auth::id())->first();
        $itenscarrinho = ItensCarrinho::where('ic_id_carrinho', $carrinho->ca_id)->get(); 
        $produtos = Produto::all();
        return view('main.usuarios.pagamento', compact('itenscarrinho', 'produtos', 'carrinho', 'endereco'));

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
    
            // Verificar se o item já está no carrinho
            $itemCarrinho = ItensCarrinho::where('ic_id_carrinho', $carrinho->ca_id)
                                        ->where('ic_id_produto', $produto_id)
                                        ->first();
    
            if ($itemCarrinho) {
                // Se o item já está no carrinho, atualizar a quantidade
                $itemCarrinho->ic_quantidade += $quantidade;
                $itemCarrinho->save();
            } else {
                // Se o item não está no carrinho, criar um novo item de carrinho
                $item = new ItensCarrinho();
                $item->ic_id_carrinho = $carrinho->ca_id;
                $item->ic_id_produto = $produto_id;
                $item->ic_quantidade = $quantidade;
                $item->save();
            }
    
            flash('Produto adicionado com sucesso!', 'success', [], 'Sucesso');
            return redirect()->back();
        } else {
            flash('Produto não adicionado!', 'error', [], 'Erro');
            return redirect()->route('login');
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
        $itemCarrinho = ItensCarrinho::find($id);
        $totalQuantidade = 0;
        $totalValor = 0;
    
        if ($itemCarrinho) {
            $itemCarrinho->ic_quantidade = $request->input('quantidade');
            $itemCarrinho->save();
    
            // Calcular total
            $itensCarrinho = ItensCarrinho::all();
            foreach ($itensCarrinho as $item) {
                $produto = Produto::find($item->ic_id_produto);
                $totalQuantidade += $item->ic_quantidade;
                $totalValor += $item->ic_quantidade * $produto->pr_preco;
            }
        }
    
        return response()->json([
            'preco_unitario' => $itemCarrinho->produto->pr_preco,
            'total_quantidade' => $totalQuantidade,
            'total_valor' => number_format($totalValor, 2, ',', '.')
        ]);
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

        flash('Produto excluído com sucesso!', 'success',[], 'Sucesso');
        
        // Redirecione de volta à página de compras ou faça qualquer outra coisa que você queira
        return back();
    }

    public function finalizarCompra($id)
    {
        // Chama o procedimento armazenado no banco de dados MySQL
        DB::statement("CALL finalizar_venda($id)");

        // Redireciona o usuário para uma página de confirmação ou outra rota
        flash('Compra finalizada com sucesso!', 'success',[], 'Sucesso');
        return redirect()->route('compra-finalizada');
    }

    public function compraFinalizada(){

        $user = Auth::user();
        return view('main.usuarios.finalizado', compact('user'));
    }
    public function confirmacaoPagamento()
    {
        // Aqui você pode retornar uma view de confirmação de pagamento
        return view('usuario.confirmacao_pagamento');
    }
}

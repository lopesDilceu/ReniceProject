<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $produtos = Produto::all(); 
        return view('adm.produtos.list', compact('produtos'));
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
        //
        $produto = Produto::create([
            'pr_nome' => $request->pr_nome,
            'pr_descricao'=>$request->pr_descricao,
            'pr_preco'=>$request->pr_preco,
        ]);

        return redirect()->route('adm.home')->with('success', 'Produto cadastrado com sucesso!');
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
                // Encontre a compra pelo ID
                $produto = Produto::findOrFail($id);

                // Exclua a compra
                $produto->delete();
        
                // Redirecione de volta à página de compras ou faça qualquer outra coisa que você queira
                return redirect()->back();
    }
}

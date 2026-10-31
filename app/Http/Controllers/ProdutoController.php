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
        $request->validate([
            'pr_nome' => 'required|string|max:255',
            'pr_descricao' => 'required|string',

            'pr_foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('pr_foto')) {
            $path = $request->file('pr_foto')->store('produtos', 'public');
            $path = 'storage/'.$path;
        } else {
            $path = null;
        }

        $produto = Produto::create([
            'pr_nome' => $request->pr_nome,
            'pr_descricao' => $request->pr_descricao,

            'pr_foto' => $path,
        ]);

        flash('Produto cadastrado com sucesso!', 'success', [], 'Sucesso');

        return redirect()->route('adm.produtos.list');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Request $request, string $id)
    {
        //
        // $produto = Produto::find($id);
        Produto::findOrFail($id)->update($request->all());
        flash('Produto atualizado com sucesso!', 'success', [], 'Sucesso');

        return back();

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

        flash('Produto excluído com sucesso!', 'success', [], 'Sucesso');

        // Redirecione de volta à página de compras ou faça qualquer outra coisa que você queira
        return back();
    }
}

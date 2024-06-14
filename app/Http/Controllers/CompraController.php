<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $compras = Compra::all(); 
        return view('adm.compras.list', compact('compras'));
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
        $compra = Compra::create([
            'co_id_produto' => $request->co_id_produto,
            'co_quantidade' => $request->co_quantidade,
            'co_preco_unitario' => $request->co_preco_unitario,
            'co_fornecedor' => $request->co_fornecedor,
        ]);

        return redirect()->back();
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
    public function destroy($id)
    {
        // Encontre a compra pelo ID
        $compra = Compra::findOrFail($id);

        // Exclua a compra
        $compra->delete();

        // Redirecione de volta à página de compras ou faça qualquer outra coisa que você queira
        return redirect()->back();
    }
}

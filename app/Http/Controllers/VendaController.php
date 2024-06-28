<?php

namespace App\Http\Controllers;

use App\Models\ItensVenda;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $vendas = Venda::all(); 
        $users = User::all();
        $itensvenda = ItensVenda::all();
        $produtos = Produto::all();
        return view('adm.vendas.list', compact('vendas', 'users', 'itensvenda', 'produtos'));
    }

    public function minhasCompras()
    {
        //
        $vendas = Venda::where('ve_id_usuario', auth()->id())->orderBy('created_at', 'desc')->get();
        $itensvenda = ItensVenda::all();
        $produtos = Produto::all();
        return view('main.usuarios.minhas-compras', compact('vendas', 'itensvenda', 'produtos'));
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
    }
}

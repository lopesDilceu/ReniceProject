<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Http\Request;

class AvaliacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Carrega todos os produtos com suas avaliações e os usuários que fizeram as avaliações
        $produtos = Produto::with(['avaliacoes.user'])->get();

        // Passa os dados para a view
        return view('main.avaliacoes', compact('produtos'));
    }


    public function listAvaliacoes()
    {
        $avaliacoes = Avaliacao::all();
        $users = User::all();
        $produtos = Produto::all();
        return view('adm.avaliacoes.list', compact('avaliacoes', 'users', 'produtos'));
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
        $avaliacao = Avaliacao::findOrFail($id);

                // Exclua a compra
                $avaliacao->delete();

                toastr()->success('Avaliação excluído com sucesso!');
        
                // Redirecione de volta à página de compras ou faça qualquer outra coisa que você queira
                return back();
    }
}

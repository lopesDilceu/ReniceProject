<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
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

    public function indexHome()
    {
        //
        $produtos = Produto::all(); 
        return view('main.home-produtos', compact('produtos'));
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

        toastr()->title('Sucesso')->success('Produto cadastrado com sucesso');

        return redirect()->route('adm.produtos.list');
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

     public function avaliarProduto($produto_id)
     {
         return view('main.usuarios.avaliacao', ['pr_id' => $produto_id]);
     }
     
     public function salvarAvaliacao(Request $request)
     {
         $avaliacao = new Avaliacao();
         $avaliacao->av_id_produto = $request->av_id_produto;
         $avaliacao->av_id_usuario = auth()->id(); // assumindo que o usuário está autenticado
         $avaliacao->av_nota = $request->av_nota;
         $avaliacao->av_comentario = $request->av_comentario;
         $avaliacao->save();
     
         return redirect()->route('minhas-compras')->with('success', 'Avaliação enviada com sucesso!');
     }
    public function edit(Request $request, string $id)
    {
        //
        // $produto = Produto::find($id);
        Produto::findOrFail($id)->update($request->all());
        toastr()->success('Produto atualizado com sucesso!');
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

                toastr()->success('Produto excluído com sucesso');
        
                // Redirecione de volta à página de compras ou faça qualquer outra coisa que você queira
                return back();
    }
}

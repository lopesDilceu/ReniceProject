<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Estoque;
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
        $produtos = Produto::join('estoque', 'produtos.pr_id', '=', 'estoque.es_id_produto')
        ->select('produtos.*')
        ->orderBy('estoque.es_quantidade', 'desc')
        ->get();
        $estoque = Estoque::all();
        return view('main.home-produtos', compact('produtos', 'estoque'));
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
            $path = 'storage/' . $path;
        } else {
            $path = null;
        }

        $produto = Produto::create([
            'pr_nome' => $request->pr_nome,
            'pr_descricao' => $request->pr_descricao,
            
            'pr_foto' => $path,
        ]);

        flash('Produto cadastrado com sucesso!', 'success',[], 'Sucesso');

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
     
         flash('Avaliação enviada com sucesso!', 'success',[], 'Sucesso');
         return redirect()->route('minhas-compras');
     }
    public function edit(Request $request, string $id)
    {
        //
        // $produto = Produto::find($id);
        Produto::findOrFail($id)->update($request->all());
        flash('Produto atualizado com sucesso!', 'success',[], 'Sucesso');
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

                flash('Produto excluído com sucesso!', 'success',[], 'Sucesso');
        
                // Redirecione de volta à página de compras ou faça qualquer outra coisa que você queira
                return back();
    }

    public function pesquisa (Request $request){
        $query = $request->input('query');

        // Assuming you have a model called "Product" and a table called "products"
        $produtos = Produto::where('pr_nome', 'LIKE', "%{$query}%")
                            ->orWhere('pr_descricao', 'LIKE', "%{$query}%")
                            ->get();
        $estoque = Estoque::all();
        return view('main.home-produtos', compact('produtos', 'estoque'));
    }
}

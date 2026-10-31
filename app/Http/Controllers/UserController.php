<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Telefone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $users = User::all();
        $telefones = Telefone::all();
        $enderecos = Endereco::all();

        return view('adm.usuarios.list', compact('users', 'telefones', 'enderecos'));
    }

    public function indexPerfil()
    {
        $user = User::find(Auth::id());

        return view('layouts.frame', compact('user'));
    }

    public function minhaConta()
    {
        if (Auth::check()) {
            $usuario = User::find(Auth::id());

            if (! $usuario) {
                // Tratar o caso de usuário não encontrado, por exemplo, redirecionar para a página de login.
                return redirect()->route('login')->with('error', 'Usuário não encontrado.');
            }
            // Recuperar telefones do usuário
            $telefones = Telefone::where('te_us_id', $usuario->us_id)->get();

            // Recuperar endereço do usuário
            $endereco = Endereco::where('en_usuario_id', $usuario->us_id)->first();

            return view('main.usuarios.show', compact('usuario', 'telefones', 'endereco'));
        } else {
            // Caso o usuário não esteja autenticado, redirecionar para a página de login
            return redirect()->route('login')->with('error', 'Você precisa estar logado para acessar esta página.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $usuario = User::find(Auth::id());
        $telefones = Telefone::where('te_us_id', $usuario->us_id)->get();

        // Recuperar endereço do usuário
        $endereco = Endereco::where('en_usuario_id', $usuario->us_id)->first();

        return view('main.usuarios.show', compact('usuario', 'telefones', 'endereco'));
    }

    public function editar()
    {
        $usuario = User::find(Auth::id());
        $telefones = Telefone::where('te_us_id', $usuario->us_id)->get();

        // Recuperar endereço do usuário
        $endereco = Endereco::where('en_usuario_id', $usuario->us_id)->first();

        return view('main.usuarios.edit', compact('usuario', 'telefones', 'endereco'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        // Validar os dados do formulário, se necessário
        $request->validate([
            'us_foto' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Exemplo de validação para imagem
            // Adicione outras regras de validação conforme necessário
        ]);

        // Encontrar o usuário pelo ID ou retornar um erro 404 se não encontrado
        $user = User::findOrFail($id);

        // Verificar se foi enviada uma nova foto e processá-la
        if ($request->hasFile('us_foto')) {
            // Armazenar a imagem na pasta 'perfil' dentro do disco 'public'
            $path = $request->file('us_foto')->store('perfil', 'public');
            // Atualizar o caminho da foto no objeto do usuário
            $user->us_foto = 'storage/'.$path;
        }

        // Atualizar outros campos do usuário, se necessário
        $user->fill($request->except('us_foto'))->save();

        // Mensagem de flash para indicar sucesso na atualização do usuário
        flash('Usuário atualizado com sucesso!', 'success');

        // Redirecionar de volta para a página anterior
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
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back();
    }
}

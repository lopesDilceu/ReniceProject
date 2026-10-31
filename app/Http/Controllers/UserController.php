<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use App\Models\Telefone;
use App\Models\User;
use Illuminate\Http\Request;

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

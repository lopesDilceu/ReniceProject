<?php

namespace App\Http\Controllers;

use App\Models\Carrinho;
use App\Models\Endereco;
use App\Models\Telefone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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


    public function minhaConta()
    {
        if (Auth::check()) {
            $usuario = User::find(Auth::id());

            if (!$usuario) {
                // Tratar o caso de usuário não encontrado, por exemplo, redirecionar para a página de login.
                return redirect()->route('login')->with('error', 'Usuário não encontrado.');
            }
            dd($usuario);
            // Recuperar telefones do usuário
            $telefones = Telefone::where('te_us_id', $usuario->us_id)->get();

            // Recuperar endereço do usuário
            $endereco = Endereco::where('en_usuario_id', $usuario->us_id)->first();

            dd($usuario);
            return view('main.usuarios.show', compact('usuario', 'telefones', 'endereco'));
        } else {
            // Caso o usuário não esteja autenticado, redirecionar para a página de login
            return redirect()->route('login')->with('error', 'Você precisa estar logado para acessar esta página.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('main.usuarios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Criar o usuário
    $user = User::create([
        'name' => $request->name,
        'us_cpf' => $request->us_cpf,
        'us_data_nasc' => $request->us_data_nasc,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // Criar telefones para o usuário
    foreach ($request->telefones as $numero) {
        Telefone::create([
            'te_us_id' => $user->us_id, // Usar a chave primária do usuário
            'te_numero' => $numero,
        ]);
    }

    // Criar endereços para o usuário
    foreach ($request->enderecos as $endereco) {
        Endereco::create([
            'en_usuario_id' => $user->us_id, // Usar a chave primária do usuário
            'en_logradouro' => $endereco['en_logradouro'],
            'en_cidade' => $endereco['en_cidade'],
            'en_estado' => $endereco['en_estado'],
            'en_numero' => $endereco['en_numero'],
            'en_cep' => $endereco['en_cep'],
        ]);
    }

    // Redirecionar para a página inicial com uma mensagem de sucesso
    return redirect()->route('usuarios.login')->with('success', 'Usuário criado com sucesso!');
}

    /**
     * Display the specified resource.
     */
    public function show($id){
        $usuario = User::find(Auth::id());
        $telefones = Telefone::where('te_us_id', $usuario->us_id)->get();

        // Recuperar endereço do usuário
        $endereco = Endereco::where('en_usuario_id', $usuario->us_id)->first();
        return view('main.usuarios.show', compact('usuario', 'telefones', 'endereco'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        User::findOrFail($id)->update($request->all());
        toastr()->success('Usuario atualizado com sucesso!');
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

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        //dd($request->all());
        if (Auth::attempt($credentials)) {
            // Autenticação bem-sucedida, redirecionar para a página inicial
            return redirect()->route('home');
        }

        // Autenticação falhou, redirecionar de volta para o formulário de login com uma mensagem de erro
        else{
            return redirect()->route('usuarios.login')->with('error', 'Credenciais inválidas.');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Redirecionar para a página de login com uma mensagem de sucesso
        return redirect()->route('usuarios.login')->with('success', 'Logout realizado com sucesso.');
    }

}

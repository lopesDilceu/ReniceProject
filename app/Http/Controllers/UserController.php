<?php

namespace App\Http\Controllers;

use App\Models\Carrinho;
use App\Models\Endereco;
use App\Models\Telefone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    public function indexPerfil(){
        $user = User::find(Auth::id());
        return view('layouts.frame', compact('user'));
    }

    public function minhaConta()
    {
        if (Auth::check()) {
            $usuario = User::find(Auth::id());

            if (!$usuario) {
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
        
        // Definindo as regras de validação
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'telefones.*' => 'required|string|max:15',
            'us_cpf' => 'required|string|size:11', // Regra para CPF com exatamente 11 caracteres
            'us_data_nasc' => 'required|date',
            'enderecos.0.en_cep' => 'required|string|size:8',
            'enderecos.0.en_logradouro' => 'required|string|max:255',
            'enderecos.0.en_numero' => 'required|string|max:10',
            'enderecos.0.en_cidade' => 'required|string|max:255',
            'enderecos.0.en_estado' => 'required|string|size:2',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'us_cpf.required' => 'O CPF é obrigatório.',
            'us_cpf.size' => 'O CPF deve ter exatamente 11 caracteres.',
            // Mensagens de validação para os outros campos
        ]);
    
        $path = $request->file('us_foto')->store('perfil', 'public');
        $path = 'storage/' . $path;
        // Verificando se a validação falhou
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Criar o usuário
        $user = User::create([
            'name' => $request->name,
            'us_cpf' => $request->us_cpf,
            'us_data_nasc' => $request->us_data_nasc,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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
        flash('Usuário criado com sucesso!', 'success',[], 'Sucesso');
        return redirect()->route('usuarios.login');
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

    public function editar(){
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
            $user->us_foto = 'storage/' . $path;
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

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        //dd($request->all());
        if (Auth::attempt($credentials)) {
            // Autenticação bem-sucedida, redirecionar para a página inicial
            // flash('Login Realizado', 'success',[], 'Sucesso');
            return redirect()->route('home');
        }

        // Autenticação falhou, redirecionar de volta para o formulário de login com uma mensagem de erro
        else{
            flash('Credenciais inválidas!', 'error',[], 'Erro');
            return redirect()->route('usuarios.login');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Redirecionar para a página de login com uma mensagem de sucesso
        
        return redirect()->route('usuarios.login');
    }

}

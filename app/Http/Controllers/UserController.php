<?php

namespace App\Http\Controllers;

use App\Models\Carrinho;
use App\Models\Endereco;
use App\Models\Telefone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // Definindo as regras de validação
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'telefones.*' => 'required|string|max:15',
            'us_cpf' => 'required|string|size:11',
            'us_data_nasc' => 'required|date',
            'enderecos.0.en_cep' => 'required|string|size:8',
            'enderecos.0.en_logradouro' => 'required|string|max:255',
            'enderecos.0.en_numero' => 'required|string|max:10',
            'enderecos.0.en_cidade' => 'required|string|max:255',
            'enderecos.0.en_estado' => 'required|string|size:2',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'telefones.*.required' => 'O telefone é obrigatório.',
            'telefones.*.max' => 'O telefone deve ter no máximo 15 caracteres.',
            'us_cpf.required' => 'O CPF é obrigatório.',
            'us_cpf.size' => 'O CPF deve ter exatamente 11 caracteres.',
            'us_data_nasc.required' => 'A data de nascimento é obrigatória.',
            'enderecos.0.en_cep.required' => 'O CEP é obrigatório.',
            'enderecos.0.en_cep.size' => 'O CEP deve ter exatamente 8 caracteres.',
            'enderecos.0.en_logradouro.required' => 'O logradouro é obrigatório.',
            'enderecos.0.en_numero.required' => 'O número é obrigatório.',
            'enderecos.0.en_cidade.required' => 'A cidade é obrigatória.',
            'enderecos.0.en_estado.required' => 'O estado é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.unique' => 'O email já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
        ]);
    
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
        toastr()->success('Usuário criado com sucesso!');
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
            toastr()->success('Login Realizado!');
            return redirect()->route('home');
        }

        // Autenticação falhou, redirecionar de volta para o formulário de login com uma mensagem de erro
        else{
            toastr()->error('Credenciais inválidas!');
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

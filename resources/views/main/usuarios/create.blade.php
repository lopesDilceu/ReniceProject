@extends('layouts.head')
@section('titulo', 'Cadastro')

@push('style')
<style>
    
    body{
      font-family: Akkurat-Mono, monospace;
    }
    
</style>
@endpush

@section('main')

<main class="container-sm container-md container-lg p-5 text-center mt-3 mb-3">
        <form class="form-register" method="POST" action="{{ route('usuarios.store') }}">
        @csrf
        <a href="{{ route('home') }}" class="link-body-emphasis text-decoration-none">
            <img src="{{asset('images/logo/renice-logo-side.png')}}" alt="renice-logo" width="30%">
        </a>
        <h1 class="h3 mb-2">CADASTRO</h1>
        <h2 class="h5 text-start">Dados Pessoais</h2>
        <div class="row g-2">
            <div class="col-md-7 col-sm-12">
                <input type="text" class="form-control mb-2" id="name" name="name" placeholder="Nome">
                <input type="text" class="form-control" id="telefone" name="telefones[]" placeholder="Telefone">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="cpf" name="us_cpf" placeholder="CPF">
                <input type="date" class="form-control mb-2" id="data-nasc" name="us_data_nasc" placeholder="Data de Nascimento">
            </div>
        </div>
        <h2 class="h5 text-start mt-2">Endereço</h2>
        <div class="row g-2">
            <div class="col-md col-sm-12">
                <input type="text" class="form-control" id="cep" name="enderecos[0][en_cep]" placeholder="CEP">
            </div>
            <div class="col-md-9 col-sm-12">
                <input type="text" class="form-control" id="rua" name="enderecos[0][en_logradouro]" placeholder="Rua">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="numero" name="enderecos[0][en_numero]" placeholder="Número">
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <input type="text" class="form-control mb-2" id="bairro" placeholder="Bairro">
            </div>
        </div>
        <div class="row g-2">
            <div class="col-md-7 col-sm-12">
                <input type="text" class="form-control mb-2" id="cidade" name="enderecos[0][en_cidade]" placeholder="Cidade">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="estado" name="enderecos[0][en_estado]" placeholder="Estado">
            </div>
        </div>
        <h2 class="h5 text-start mt-2">Dados de Login</h2>
            <input type="email" class="form-control mb-2" id="email" name="email" placeholder="Email">
            <input type="password" class="form-control mb-4" id="senha" name="password" placeholder="Senha">
        <div class="d-grid d-md-flex gap-2 justify-content-md-end">
            <button type="button" class="btn btn-outline-secondary" id="voltar">Voltar </button>
            <button class="w-80 btn btn-lg btn-dark" type="submit">Cadastrar</button>
        </div>
        <p class="mt-3 mb-1 text-muted">&copy; <a href="https://github.com/lopesDilceu" class="text-decoration-none text-body-secondary">2024 - Dilceu</a></p>
    </form>
</main>

<script>
    document.getElementById('voltar').addEventListener('click', function(event) {
        event.preventDefault();
        window.location.href = "{{ route('usuarios.login')}}";
    });
</script>


@endsection
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
    <form class="form-register">
        <a href="{{ route('home') }}" class="link-body-emphasis text-decoration-none">
            <img src="{{asset('images/logo/renice-logo.png')}}" alt="renice-logo" width="300" height="300">
        </a>
        <h1 class="h3 mb-2">CADASTRO</h1>
        <h2 class="h5 text-start">Dados Pessoais</h2>
        <div class="row g-2">
            <div class="col-md-7 col-sm-12">
                <input type="text" class="form-control mb-2" id="nome" placeholder="Nome">
                <input type="text" class="form-control" id="telefone" placeholder="Telefone">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="cpf" placeholder="CPF">
                <input type="date" class="form-control mb-2" id="data-nasc" placeholder="Data de Nascimento">
            </div>
        </div>
        <h2 class="h5 text-start mt-2">Endereço</h2>
        <div class="row g-2">
            <div class="col-md col-sm-12">
                <input type="text" class="form-control" id="cep" placeholder="CEP">
            </div>
            <div class="col-md-9 col-sm-12">
                <input type="text" class="form-control" id="rua" placeholder="Rua">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="numero" placeholder="Número">
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <input type="text" class="form-control mb-2" id="bairro" placeholder="Bairro">
            </div>
        </div>
        <div class="row g-2">
            <div class="col-md-7 col-sm-12">
                <input type="text" class="form-control mb-2" id="cidade" placeholder="Cidade">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="estado" placeholder="Estado">
            </div>
        </div>
        <h2 class="h5 text-start mt-2">Dados de Login</h2>
        <input type="email" class="form-control mb-2" id="email" placeholder="Email">
        <input type="password" class="form-control mb-4" id="senha" placeholder="Senha">
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
        window.location.href = '{{ route('usuarios.login') }}';
    });
</script>


@endsection
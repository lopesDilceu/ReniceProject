@extends('layouts.head')
@section('titulo', 'Cadastro')

@push('style')
<style>
    body {
        font-family: Akkurat-Mono, monospace;
    }
</style>
@endpush

@section('main')

<main class="container-sm container-md container-lg p-5 text-center mt-3 mb-3">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="form-register" method="POST" action="{{ route('usuarios.store') }}">
        @csrf
        <a href="{{ route('home') }}" class="link-body-emphasis text-decoration-none">
            <img src="{{ asset('images/logo/renice-logo-side.png') }}" alt="renice-logo" width="30%">
        </a>
        <h1 class="h3 mb-2">CADASTRO</h1>
        <h2 class="h5 text-start">Dados Pessoais</h2>
        <div class="row g-2">
            <div class="col-md-7 col-sm-12">
                <input type="text" class="form-control mb-2" id="name" name="name" placeholder="Nome" value="{{ old('name') }}">
                <input type="text" class="form-control" id="telefone" name="telefones[]" placeholder="Telefone" value="{{ old('telefones.0') }}">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="cpf" name="us_cpf" placeholder="CPF" value="{{ old('us_cpf') }}">
                <input type="date" class="form-control mb-2" id="data-nasc" name="us_data_nasc" placeholder="Data de Nascimento" value="{{ old('us_data_nasc') }}">
            </div>
        </div>
        <h2 class="h5 text-start mt-2">Endereço</h2>
        <div class="row g-2">
            <div class="col-md col-sm-12">
                <input type="text" class="form-control" id="cep" name="enderecos[0][en_cep]" placeholder="CEP" value="{{ old('enderecos.0.en_cep') }}">
            </div>
            <div class="col-md-9 col-sm-12">
                <input type="text" class="form-control" id="rua" name="enderecos[0][en_logradouro]" placeholder="Rua" value="{{ old('enderecos.0.en_logradouro') }}">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="numero" name="enderecos[0][en_numero]" placeholder="Número" value="{{ old('enderecos.0.en_numero') }}">
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <input type="text" class="form-control mb-2" id="bairro" name="enderecos[0][en_bairro]" placeholder="Bairro" value="{{ old('enderecos.0.en_bairro') }}">
            </div>
        </div>
        <div class="row g-2">
            <div class="col-md-7 col-sm-12">
                <input type="text" class="form-control mb-2" id="cidade" name="enderecos[0][en_cidade]" placeholder="Cidade" value="{{ old('enderecos.0.en_cidade') }}">
            </div>
            <div class="col-md col-sm-12">
                <input type="text" class="form-control mb-2" id="estado" name="enderecos[0][en_estado]" placeholder="Estado" value="{{ old('enderecos.0.en_estado') }}">
            </div>
        </div>
        <h2 class="h5 text-start mt-2">Dados de Login</h2>
        <input type="email" class="form-control mb-2" id="email" name="email" placeholder="Email" value="{{ old('email') }}">
        <input type="password" class="form-control mb-4" id="senha" name="password" placeholder="Senha">
        <input type="password" class="form-control mb-4" id="password_confirmation" name="password_confirmation" placeholder="Confirme a Senha">
        <div class="d-grid d-md-flex gap-2 justify-content-md-end">
            <button type="button" class="btn btn-outline-secondary" id="voltar">Voltar</button>
            <button class="w-80 btn btn-lg btn-dark" type="submit">Cadastrar</button>
        </div>
        <p class="mt-3 mb-1 text-muted">&copy; <a href="https://github.com/lopesDilceu" class="text-decoration-none text-body-secondary">2024 - Dilceu</a></p>
    </form>
</main>

<script>
    document.getElementById('voltar').addEventListener('click', function(event) {
        event.preventDefault();
        window.location.href = "{{ route('usuarios.login') }}";
    });
</script>


@push('script')
<script>
document.querySelector('.form-register').addEventListener('submit', function(event) {
    let isValid = true;
    let messages = [];

    // Validações simples de exemplo
    const name = document.getElementById('name').value;
    if (!name) {
        isValid = false;
        messages.push('Nome é obrigatório.');
    }

    const email = document.getElementById('email').value;
    if (!email) {
        isValid = false;
        messages.push('Email é obrigatório.');
    } else if (!validateEmail(email)) {
        isValid = false;
        messages.push('Email inválido.');
    }

    const password = document.getElementById('senha').value;
    if (!password || password.length < 6) {
        isValid = false;
        messages.push('Senha deve ter no mínimo 6 caracteres.');
    }

    // Se o formulário não for válido, exibe mensagens e previne o envio
    if (!isValid) {
        event.preventDefault();
        alert(messages.join('\n'));
    }
});

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

</script>


@endpush

@endsection

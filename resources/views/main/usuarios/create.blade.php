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

    <form id="formCreate" class="form-register" method="POST" action="{{ route('usuarios.store') }}">
        @csrf
        <div class="mb-4">
            <a href="{{ route('home') }}" class="link-body-emphasis text-decoration-none">
                <img src="{{ asset('images/logo/renice-logo-side.png') }}" alt="renice-logo" width="20%">
            </a>
        </div>
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
        <input type="password" class="form-control mb-2" id="senha" name="password" placeholder="Senha">
        <input type="password" class="form-control mb-4" id="password_confirmation" name="password_confirmation" placeholder="Confirme a Senha">
        <section>
            @error('name')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('telefones[]')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('us_cpf')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('us_data_nasc')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('enderecos[0][en_cep]')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('enderecos[0][en_logradouro]')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('enderecos[0][en_numero]')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('enderecos[0][en_bairro]')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('enderecos[0][en_cidade]')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('enderecos[0][en_estado]')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('email')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('password')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('password_confirmation')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </section>
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
    $('#cpf').mask('000.000.000-00');
    $('#telefone').mask('(00) 00000-0000');
    $('#cep').mask('00000-000');
    $('#formCreate').submit(function(event) {
        // Remover a máscara para obter o valor real do CPF
        //let cpf = $('#cpf').val().replace(/\D/g, '');  //Remove todos os não dígitos

        // Verificar se o CPF tem exatamente 11 caracteres
        if (unmask(cpf.length) !== 11) {
            alert('O CPF deve ter exatamente 11 dígitos.');
            event.preventDefault(); // Impede o envio do formulário
        }

        // Remover a máscara para enviar o valor correto
        $('#cpf').unmask();
        $('#telefone').unmask();
        $('#cep').unmask();
    });

    $('#cep').blur(function() {
        let cep = $('#cep').unmask().val();
        $('#cep').mask('00000-000');
        $.get('https://viacep.com.br/ws/'+ cep +'/json/', function(dados) {
            $('#rua').val(dados.logradouro);
            $('#bairro').val(dados.bairro);
            $('#cidade').val(dados.localidade);
            $('#estado').val(dados.uf);
        });
    });

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

</script>


@endpush

@endsection

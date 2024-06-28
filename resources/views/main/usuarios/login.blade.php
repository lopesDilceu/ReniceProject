@extends('layouts.head')
@section('titulo', 'Login')

@push('style')
<style>
    body {
        font-family: Akkurat-Mono, monospace;
    }

    .form-container {
        max-width: 500px;
        margin: 0 auto;
    }
</style>
@endpush

@section('main')

<main class="form-signin text-center">
    <form method="POST" action="{{ route('usuarios.login.submit') }}" class="needs-validation form-container">
        @csrf 
        <a href="{{ route('home') }}" class="link-body-emphasis text-decoration-none">
            <img class="mb-4" src="{{ asset('images/logo/renice-logo.png') }}" alt="renice-logo" width="60%">
        </a>
        <h3 class="h3 mb-3">LOGIN</h3>
        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
            <label for="floatingInput">Email</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
            <label for="floatingPassword">Senha</label>
        </div>

        <div class="row mb-3">
            <div class="col">
                <button type="button" class="w-100 btn btn-outline-secondary" id="voltar">Voltar</button>
            </div>
            <div class="col">
                <button class="w-100 btn btn-dark" type="submit">Entrar</button>
            </div>
        </div>
        <p class="mt-3">Não tem cadastro? <a class="text-indigo text-body-secondary text-decoration-none" href="{{ route('usuarios.cadastro') }}">Cadastre-se</a></p>
        <p class="mt-3 mb-3 text-muted">&copy; <a href="https://github.com/lopesDilceu" class="text-decoration-none text-body-secondary">2024 - Dilceu</a></p>
    </form>

    <script>
        document.getElementById('voltar').addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = "{{ route('home') }}";
        });
    </script>
</main>

@endsection

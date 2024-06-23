@extends('layouts.head')
@section('titulo', 'Login')

@push('style')
<style>
    
    body {
      font-family: Akkurat-Mono, monospace;
    }
    
</style>
@endpush

@section('main')

<main class="form-signin text-center">
  <form method="POST" action="{{ route('usuarios.login.submit') }}" class="needs-validation">
    @csrf 
    <a href="{{ route('home') }}" class="link-body-emphasis text-decoration-none">
      <img class="mb-2" src="{{asset('images/logo/renice-logo.png')}}" alt="renice-logo" width="300" height="300">
    </a>
    <h3 class="h3 mb-3">LOGIN</h3>
    <div class="form-floating mb-2 container">
      <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
      <label for="floatingInput" style="margin-left: 5px;">Email</label>
    </div>
    <div class="form-floating mb-2 container">
      <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
      <label for="floatingPassword" style="margin-left: 5px;">Senha</label>
    </div>

    <div class="checkbox mb-3 mt-3">
      <label>
        <input type="checkbox" value="remember-me"> Lembrar de mim
      </label>
    </div>
    <div >
      <button type="button" class="w-80 btn btn-lg btn-outline-secondary" id="voltar">Voltar </button>
      <button class="w-80 btn btn-lg btn-dark" type="submit">Entrar</button>
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

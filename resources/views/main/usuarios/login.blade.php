@extends('layouts.head')
@section('titulo', 'Login')

@section('main')

<main class="form-signin text-center">
  <form class="">
    <a href="{{ route('home') }}" class="link-body-emphasis text-decoration-none">
      <img class="mb-2" src="images/logo/renice-logo.png" alt="renice-logo" width="300" height="300">
    </a>
    <h3 class="h3 mb-3">LOGIN</h3>

    <div class="form-floating mb-2 container">
      <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
      <label for="floatingInput" style="margin-left: 5px;">Email</label>
    </div>
    <div class="form-floating mb-2 container">
      <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
      <label for="floatingPassword" style="margin-left: 5px;">Senha</label>
    </div>

    <div class="checkbox mb-3 mt-3">
      <label>
        <input type="checkbox" value="remember-me"> Lembrar de mim
      </label>
    </div>
    <button class="w-80 btn btn-lg btn-dark" type="submit">Entrar</button>
    <p class="mt-3">Não tem cadastro? <a class="text-indigo text-body-secondary text-decoration-none" href="{{ route('usuarios.cadastro') }}">Cadastre-se</a></p>
    <p class="mt-3 mb-3 text-muted">&copy; <a href="https://github.com/lopesDilceu" class="text-decoration-none text-body-secondary">2024 - Dilceu</a></p>
  </form>
</main>


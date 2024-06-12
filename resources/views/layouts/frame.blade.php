@extends('layouts.head')

@push('style')
<style>
    .home-container {
        position: relative;
    }

    .home {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        background-color: rgba(255, 255, 255, 0.96); /* Cor de fundo semi-transparente */
        padding: 10px;
        border-radius: 5px;
    }

    .home-in {
        position: absolute;
        bottom: 10px; /* Posiciona o elemento a 10px do fundo da div */
        right: 4px;  /* Posiciona o elemento a 10px da direita da div */
        background-color: rgba(255, 255, 255, 0); /* Cor de fundo semi-transparente */
        padding: 10px;
        border-radius: 10px;
    }
</style>
@endpush



@section('main')


  <header class="p-3 mb-3 border-bottom h-25">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a href="/" class="d-flex align-items-center me-2 mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
            <img src="{{ asset('images/logo/renice-logo-side.png')}}" alt="renice-logo" width="100" height="40">
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav me-auto my-2 my-lg-0">
              <li class="nav-item">
                <a class="nav-link @yield('home')" href="{{ route('home') }}">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link @yield('produtos')" href="{{ route('produtos') }}">Produtos</a>
              </li>
              <li class="nav-item">
                <a class="nav-link  @yield('sobre')" href="{{ route('sobre') }}">Sobre</a>
              </li>
            </ul>
            <div class="d-flex justify-content-center">
              <!-- <form class="d-flex flex-fill me-2" role="search">
                <div class="input-group">
                  <span class="input-group-text">
                    <img src="{{ asset('images/icons/search.svg') }}" alt="" width="12" height="12">
                  </span>
                  <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                </div>
              </form> -->
              <div class="dropdown text-end">
                <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  <img src="{{ asset('images/perfil.png')}}" alt="perfil" width="30" height="30">
                </a>
                <ul class="dropdown-menu text-small">
                  <li><a class="dropdown-item" href="{{route('usuarios.login')}}">Entrar</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </nav>
  </header>
  
  <div class="container mb-4">
    @yield('content')
  </div>
  
  <footer class="border-top">
      <div class="container d-flex flex-wrap justify-content-between align-items-center py-1 my-4">
          <p class="col-md-4 mb-0 text-body-secondary">&copy; 2024 - <a href="https://github.com/lopesDilceu" target="_blank" class="text-decoration-none text-body-secondary">Dilceu Lopes</a></p>
          <a href="{{ route('home') }}" class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
              <img src="{{ asset('images/logo/renice-icon.png')}}" alt="renice-icon" width="60" height="60">
          </a>
          <ul class="nav col-md-4 justify-content-end">
            <li class="nav-item"><a href="{{ route('home') }}" class="nav-link px-2 text-body-secondary">Home</a></li>
            <li class="nav-item"><a href="{{ route('sobre') }}" class="nav-link px-2 text-body-secondary">Sobre</a></li>
            <li class="nav-item"><a href="{{ route('adm.home')}}" class="nav-link px-2 text-body-secondary">Administração</a></li>
          </ul>
      </div>
  </footer>

@endsection


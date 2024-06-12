
@section('titulo', 'Home')

  @extends('layouts.frame')
  @section('home', 'active')
  @section('content')
  @include('layouts.components.produto-modal')
    <h1 class="h1 mb-4 text-center">Bem-Vindo</h1>
    <div class="text-center home-container" style="position: relative;">
        <img src="{{asset('images/gelo2.png')}}" alt="gelo" style="width: 100%;">
        <div class="home" style="width:400px; height:600px">
            <h6>
                <p>
                    <!-- <img src="{{asset('images/logo/renice-icon.png')}}" alt="renice-logo" width="30%"> -->
                    <br>
                    <h2>Sobre a loja</h2>
                    Loja especializada na confecção e venda de copos com gelo.
                </p>
                <p>Nossos copos são comercializados prontos.</p>
                <p>Nossa missão é facilitar e proporcionar o melhor conforto ao cliente no momento de consumir sua bebida.</p>
            </h6>
            <div class="home-in">
            <img src="{{asset('images/logo/renice-logo-side.png')}}" alt="copo" width="20%" class="rounded">
            </div>
        </div>
    </div>

    
    
  @endsection



@section('titulo', 'Home')

  @extends('layouts.frame')
  @section('home', 'active')
  @section('content')
    <h1 class="h1 mb-4 text-center" style="font-family: Akkurat-Mono, monospace;" >BEM-VINDO</h1>
    <div class=" home-container" >
        <img src="{{asset('images/gelo2.png')}}" alt="gelo" style="width: 100%;">
        <div class="home " style="width:400px; height:600px">
            <h6>
                <p>
                    <!-- <img src="{{asset('images/logo/renice-icon.png')}}" alt="renice-logo" width="30%"> -->
                    <br>
                    <h2 class="text-center" >SOBRE A LOJA</h2><br><br>
                    <h1><strong>LEMBRAR DE ALTERAR PR_PRECO PARA NULL DEFAULT 0.00 </strong></h1>
                    Loja especializada na confecção e venda de copos com gelo.
                </p>
                <p>Nossos copos são comercializados prontos.</p>
                <p>Nossa missão é facilitar e proporcionar o melhor conforto ao cliente no momento de consumir sua bebida.</p>
            </h6>
            <div class="home-in text-center">
            <img src="{{asset('images/logo/renice-logo-side.png')}}" alt="copo" width="20%" class="rounded">
            </div>
        </div>
    </div>

    
    
  @endsection


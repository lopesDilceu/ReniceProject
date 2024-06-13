
@section('titulo', 'Home')

  @extends('layouts.frame')
  @section('produtos', 'active')
  @section('content')
  @include('layouts.components.produto-modal')
    <h1 class="h1 mb-4">PRODUTOS</h1>
    <div class="row" data-masonry='{"percentPosition": true }'>
        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <img src="{{ asset('images/logo/renice-logo-round.png') }}" alt="" class="rounded-top">
                <div class="card-body">
                    <h5 class="card-title">500ml - gelo pequeno</h5>
                    <p class="card-text">R$ 05,00</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <div class="col-3 col-sm-2">
                          <input type="number" class="form-control" id="productQuantity" name="productQuantity" min="1" max="100" step="1" value="1">
                        </div>
                        <button class="btn btn-outline-secondary">Adicionar ao Carrinho</button>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <img src="{{ asset('images/logo/renice-logo-round.png') }}" alt="" class="rounded-top">
                <div class="card-body">
                    <h5 class="card-title">500ml - gelo médio</h5>
                    <p class="card-text">R$ 05,00</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <div class="col-3 col-sm-2">
                          <input type="number" class="form-control" id="productQuantity" name="productQuantity" min="1" max="100" step="1" value="1">
                        </div>
                        <button class="btn btn-outline-secondary">Adicionar ao Carrinho</button>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <img src="{{ asset('images/logo/renice-logo-round.png') }}" alt="" class="rounded-top">
                <div class="card-body">
                    <h5 class="card-title">500ml - gelo grande</h5>
                    <p class="card-text">R$ 05,00</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <div class="col-3 col-sm-2">
                          <input type="number" class="form-control" id="productQuantity" name="productQuantity" min="1" max="100" step="1" value="1">
                        </div>
                        <button class="btn btn-outline-secondary">Adicionar ao Carrinho</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    
    
  @endsection


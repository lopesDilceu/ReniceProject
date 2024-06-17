
@section('titulo', 'Produtos')
@section('produtos', 'active')
@extends('layouts.frame')
  
  @section('content')
  @include('layouts.components.produto-modal')
    <h1 class="h1 mb-4">PRODUTOS</h1>
    <div class="row" data-masonry='{"percentPosition": true }'>
        @foreach ($produtos as $produto)    
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card shadow-sm">
                <img src="{{ asset($produto->pr_foto) }}" alt="" class="rounded-top" height="270px">
                <div class="card-body">
                    <h5 class="card-title">{{$produto->pr_nome}}</h5>
                    <p class="card-text">R$ {{$produto->pr_preco}}</p>
                    <form action="{{ route('carrinho.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="pr_id" value="{{ $produto->pr_id }}">
                        <div class="d-flex gap-2 justify-content-center">
                            <div class="col-3 col-sm-2" style="min-width: 60px;">
                                <input type="number" class="form-control" name="ic_quantidade" min="1" max="100" step="1" value="1">
                            </div>
                            <button type="submit" class="btn btn-outline-success">ADCIONAR <img src="{{asset('/images/icons/carrinho.png')}}" alt="addToCarrinho" width="24px" height="24px"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    
    
  @endsection


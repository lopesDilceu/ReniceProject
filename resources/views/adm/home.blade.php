@section('titulo', 'Administração')


@extends('layouts.frame')

@push('style')


@endpush


@section('content')
<h1 class="h1 mb-4 text-center" style="font-family: Akkurat-Mono, monospace;">ADMINISTRAÇÃO</h1>
<div class=" home-container text-center" style="height: 660px">
    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('usuarios.show')}}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
    <div class="row" data-masonry='{"percentPosition": true }'>
        <div class="col-sm-6 col-lg-4 mb-4 ">
            <div class="card shadow-sm ">
                <div class="text-center">
                    <img src="{{ asset('images/icons/avaliacoes.png') }}" alt="" class="rounded-top p-2" width="35%">
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-center">

                        <a href="{{ route('adm.avaliacoes.list') }}" class="btn btn-outline-secondary">AVALIAÇÕES</a>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="text-center">
                    <img src="{{ asset('images/icons/compras.png') }}" alt="" class="rounded-top p-2" width="35%">
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-center">

                        <a href="{{ route('adm.compras.list') }}" class="btn btn-outline-secondary">COMPRAS</a>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="text-center">
                    <img src="{{ asset('images/icons/estoque.png') }}" alt="" class="rounded-top p-2" width="35%">
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-center">

                        <a href="{{ route('adm.estoque.list') }}" class="btn btn-outline-secondary">ESTOQUE</a>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <div class="row" data-masonry='{"percentPosition": true }'>
        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="text-center">
                    <img src="{{ asset('images/icons/produtos.png') }}" alt="" class="rounded-top p-2" width="35%">
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-center">

                        <a href="{{ route('adm.produtos.list') }}" class="btn btn-outline-secondary">PRODUTOS</a>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm ">
                <div class="text-center">
                    <img src="{{ asset('images/icons/usuarios.png') }}" alt="" class="rounded-top p-2" width="35%">
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-center">

                        <a href="{{ route('adm.usuarios.list') }}" class="btn btn-outline-secondary">USUÁRIOS</a>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="text-center">
                    <img src="{{ asset('images/icons/vendas.png') }}" alt="" class="rounded-top p-2" width="35%">
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-center">

                        <a href="{{ route('adm.vendas.list') }}" class="btn btn-outline-secondary">VENDAS</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>



@endsection
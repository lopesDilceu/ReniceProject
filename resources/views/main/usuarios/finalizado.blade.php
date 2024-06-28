@extends('layouts.frame')

@section('titulo', 'Finalizado')

@section('content')
<div>
    <h1 class="h1 mb-4 mt-2 text-center">COMPRA FINALIZADA</h1>
</div>
<div class="row mb-4" style="height: 680px">
    <div class="col-12 d-flex flex-column justify-content-center">
        <div class="text-center mb-5 " style="margin-top: 150px;">
            <h3 class="h3 mb-4">{{ $user->name }}, Obrigado por sua compra!</h3>
            <p class="">Em nome de toda a equipe, gostariamos de agradecer pela preferência e parabenizar pelo bom gosto na escolha.</p>
            <p>Nossos produtos foram pensados para agradar o público que buscam otimizar seus momentos de lazer e aproveitar ao máximo.</p>
            <p>Se você tiver qualquer dúvida ou precisar de assistência, entre em contato com o nosso suporte ao cliente.</p>
            <span class="lead">Atenciosamente, Equipe</span>
            <img src="{{ asset('images/logo/renice-logo-side.png') }}" alt="ReniceLogo" class="p-2" width="10%"><br><br>
        </div>
        <div class="mt-auto text-center">
            <a href="{{ route('home') }}" class="btn btn-outline-dark mt-3">Página Inicial</a>
        </div>
    </div>
</div>
@endsection

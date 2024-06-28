@extends('layouts.frame')

@section('titulo', 'Avaliações')
@section('avaliacoes', 'active')

@push('style')
    <style>
        .produto-avaliacoes {
            max-height: 600px;
            overflow-y: scroll;
        }
        .horizontal-scroll {
            display: flex;
            overflow-x: auto;
        }
        .horizontal-scroll .card {
            min-width: 300px;
            margin-right: 20px;
        }
        
    </style>
@endpush

@section('content')
    <h1 class="h1 mb-4">AVALIAÇÕES</h1>
    <div class="container" style="min-height: 680px;">
        @if($produtos->isNotEmpty())
            @foreach($produtos as $produto)
                @if($produto->avaliacoes->isNotEmpty())
                    <div class="row mb-4">
                        <div class="col-12" >
                            <div >
                                <div class="card shadow-sm"  >
                                    <div class="card-header text-center">
                                        <img src="{{asset($produto->pr_foto)}}" alt="" class="rounded-circle mb-2" width="150" height="150">
                                        <h5 class="card-title text-center">{{ $produto->pr_nome }}</h5>
                                    </div>
                                    <div class="card-body produto-avaliacoes">
                                        <div class="horizontal-scroll">
                                            @foreach($produto->avaliacoes as $avaliacao)
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <h6 class="card-subtitle mb-2 text-muted">
                                                            Nota: {{ $avaliacao->av_nota }}/5
                                                            <br>
                                                            Usuário: {{ $avaliacao->user->name }}
                                                        </h6>
                                                        <p class="card-text">Comentário: {{ $avaliacao->av_comentario }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <p class="text-center">Nenhuma avaliação encontrada.</p>
        @endif
    </div>
@endsection
